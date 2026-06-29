<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\ConsentForm;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\AppointmentCompletionNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    private const DAILY_CONFIRMED_LIMIT = 10;

    private const ONLINE_TIME_SLOTS = [
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
    ];

    /*
    |--------------------------------------------------------------------------
    | ONLINE BOOKING (CUSTOMER)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $participantServiceIds = collect($request->input('participants', []))->pluck('service_ids')->flatten()->filter()->unique()->values()->all();
        $request->merge([
            'contact_number' => preg_replace('/\s+/', '', (string) $request->input('contact_number')),
            'service_ids' => $request->input('service_ids', $participantServiceIds ?: ($request->filled('service_id') ? [$request->input('service_id')] : [])),
            'party_size' => count($request->input('participants', [])) ?: $request->input('party_size', 1),
        ]);

        if ($request->filled('service_id') && ! Service::whereKey($request->input('service_id'))->where('is_active', true)->exists()) {
            return back()->withInput()->withErrors(['service_id' => 'The selected service is not available.']);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1|max:50',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => ['integer', 'distinct', Rule::exists('services', 'id')->where('is_active', true)],
            'participants' => 'nullable|array|min:1|max:50',
            'participants.*.name' => 'nullable|string|max:255',
            'participants.*.service_ids' => 'required_with:participants|array|min:1',
            'participants.*.service_ids.*' => ['integer', Rule::exists('services', 'id')->where('is_active', true)],
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            'consent_accepted' => 'nullable|boolean',
            'consent_signature' => 'nullable|string|max:3000000',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
            'date.after_or_equal' => 'Please choose today or a future date.',
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->get();
        $service = $services->first();
        if ($services->contains(fn ($item) => (int) $item->session_count > 1)
            && ($services->count() > 1 || $validated['party_size'] > 1)) {
            return back()->withInput()->withErrors([
                'service_ids' => 'A multi-session package must be booked alone for one client.',
            ]);
        }
        if ($this->confirmedCountForDate($validated['date']) >= self::DAILY_CONFIRMED_LIMIT) {
            return back()
                ->withInput()
                ->withErrors([
                    'date' => 'We are fully booked for that date. Please try again tomorrow, choose another date, or try walk-in.',
                ]);
        }

        if (! in_array($validated['time'], self::ONLINE_TIME_SLOTS, true)) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'Please choose one of the available online appointment times.']);
        }

        DB::transaction(function () use ($validated, $service, $services) {
            $client = $this->resolveClient($validated);
            $appointment = Appointment::create([
                'full_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['email'] ?? null,
                'party_size' => $validated['party_size'],
                'client_id' => $client->id,
                'service_id' => $service->id,
                'price_at_booking' => $this->participantBookingTotal($validated, $services),
                'date' => $validated['date'],
                'time' => $validated['time'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            $this->syncAppointmentServices($appointment, $services);
            $this->syncAppointmentParticipants($appointment, $validated, $services);

            $this->attachServicePackage($appointment, $service, $client);

        });

        $destination = Auth::check() && Auth::user()->role === 'customer'
            ? route('customer.bookings.index')
            : '/#book';

        return redirect($destination)->with('success', 'Appointment submitted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $user = Auth::user();
        if ($user->role === 'customer') {
            return redirect()->route('customer.bookings.index');
        }

        $query = Appointment::query();

        if (in_array($user->role, ['admin', 'management', 'reception'])) {
            $appointments = $query->latest()->get();
        } elseif ($user->role === 'staff') {
            $appointments = $query->whereHas('assignedStaffMembers', fn ($staff) => $staff->where('users.id', $user->id))->latest()->get();
        } else {
            $appointments = $query
                ->whereHas('client', fn ($client) => $client->where('user_id', $user->id))
                ->latest()
                ->get();
        }

        return view('dashboard', compact('appointments'));
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        if (! in_array(Auth::user()->role, ['admin', 'management', 'staff', 'reception'])) {
            abort(403);
        }

        $appointments = Appointment::with(['service', 'services', 'participants.services', 'participants.payments', 'servicePackage', 'assignedStaffMembers', 'invoice.payments', 'consentForm'])
            ->when(Auth::user()->role === 'staff', function ($query) {
                $query->whereHas('assignedStaffMembers', fn ($staff) => $staff->where('users.id', Auth::id()));
            })
            ->latest()
            ->paginate(8);
        $staff = User::where('role', 'staff')->orderBy('name')->get();
        $services = Service::all();
        $consentRecords = $appointments->getCollection()
            ->filter(fn ($appointment) => $appointment->consentForm)
            ->mapWithKeys(fn ($appointment) => [
                $appointment->id => [
                    'name' => $appointment->consentForm->full_name,
                    'contact' => $appointment->consentForm->contact_number,
                    'email' => $appointment->consentForm->email,
                    'service' => $appointment->service->name ?? 'Service',
                    'signed_at' => optional($appointment->consentForm->signed_at)->format('M d, Y h:i A'),
                    'consent_text' => $appointment->consentForm->consent_text,
                    'signature_url' => route('appointments.consent.signature', $appointment->id),
                ],
            ]);
        $appointmentRecords = $appointments->getCollection()->mapWithKeys(fn ($appointment) => [
            $appointment->id => [
                'id' => $appointment->id,
                'full_name' => $appointment->full_name,
                'contact_number' => $appointment->contact_number,
                'email' => $appointment->email,
                'party_size' => $appointment->party_size,
                'per_client_total' => $appointment->per_client_total,
                'clients_paid' => (int) ($appointment->billing_invoice?->payments
                    ?->where('payment_scope', 'per_client')->sum('client_count') ?? 0),
                'participants' => $appointment->participants->map(fn ($participant) => [
                    'id' => $participant->id,
                    'name' => $participant->display_name,
                    'services' => $participant->services->pluck('name')->join(', '),
                    'total' => $participant->total,
                    'paid' => (float) $participant->payments->sum(fn ($payment) => (float) $payment->pivot->amount),
                ])->values(),
                'service_id' => $appointment->service_id,
                'service_ids' => $appointment->services->pluck('id')->values(),
                'service_name' => $appointment->service_names,
                'service_price' => $appointment->services_total,
                'date' => $appointment->date,
                'time' => substr((string) $appointment->time, 0, 5),
                'display_time' => $appointment->formatted_time,
                'notes' => $appointment->notes,
                'status' => $appointment->status,
                'booking_type' => $appointment->booking_type ?? 'online',
                'assigned_staff_ids' => $appointment->assignedStaffMembers->pluck('id')->values(),
                'assigned_staff' => $appointment->assigned_staff_names,
                'payment_status' => $appointment->payment_status ?? 'unpaid',
                'has_invoice' => (bool) $appointment->billing_invoice || (bool) $appointment->service_package_id,
                'billing_invoice_id' => $appointment->billing_invoice?->id,
                'billing_total' => (float) ($appointment->billing_invoice?->grand_total
                    ?? $appointment->servicePackage?->total_price
                    ?? $appointment->price_at_booking
                    ?? 0),
                'billing_paid' => (float) ($appointment->billing_invoice?->amount_paid ?? 0),
                'billing_balance' => (float) ($appointment->billing_invoice?->balance
                    ?? $appointment->servicePackage?->total_price
                    ?? $appointment->price_at_booking
                    ?? 0),
                'package' => $appointment->servicePackage ? [
                    'id' => $appointment->servicePackage->id,
                    'total_sessions' => $appointment->servicePackage->total_sessions,
                    'used_sessions' => $appointment->servicePackage->used_sessions,
                    'remaining_sessions' => $appointment->servicePackage->remaining_sessions,
                ] : null,
                'requires_consent' => $appointment->services->contains('requires_consent', true),
                'has_consent' => (bool) $appointment->consentForm,
            ],
        ]);

        return response()
            ->view('appointments.index', compact('appointments', 'staff', 'services', 'consentRecords', 'appointmentRecords'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function update(Request $request, $id)
    {
        if (! in_array(Auth::user()->role, ['admin', 'management', 'reception'])) {
            abort(403);
        }

        $request->merge([
            'contact_number' => preg_replace('/\s+/', '', (string) $request->input('contact_number')),
            'service_ids' => $request->input('service_ids', $request->filled('service_id') ? [$request->input('service_id')] : []),
            'party_size' => $request->input('party_size', 1),
        ]);

        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1|max:50',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'integer|distinct|exists:services,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'assigned_staff_ids' => 'nullable|array',
            'assigned_staff_ids.*' => 'integer|distinct|exists:users,id',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
        ]);

        if (! $this->areStaffMembers($validated['assigned_staff_ids'] ?? [])) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            $completedAppointment = DB::transaction(function () use ($id, $validated) {
                $appointment = Appointment::with(['invoice', 'services', 'consentForm'])->findOrFail($id);
                $oldStatus = $appointment->status;
                $serviceIds = collect($validated['service_ids'])->map(fn ($id) => (int) $id)->sort()->values();
                $oldServiceIds = $appointment->services->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
                if ($validated['status'] === 'completed' && $appointment->services->contains('requires_consent', true) && ! $appointment->consentForm) {
                    throw new \RuntimeException('CONSENT_REQUIRED');
                }

                if ($appointment->invoice && $oldServiceIds->all() !== $serviceIds->all()) {
                    throw new \RuntimeException('PAID_SERVICE_CHANGE_BLOCKED');
                }

                if ($appointment->service_package_id && ($serviceIds->count() !== 1 || $validated['party_size'] > 1 || (int) $appointment->service_id !== $serviceIds->first())) {
                    throw new \RuntimeException('PACKAGE_SERVICE_CHANGE_BLOCKED');
                }

                if (
                    $validated['status'] === 'confirmed' &&
                    $appointment->status !== 'confirmed' &&
                    $this->confirmedCountForDate($validated['date'], $appointment->id) >= self::DAILY_CONFIRMED_LIMIT
                ) {
                    throw new \RuntimeException('DAILY_CONFIRMED_LIMIT_REACHED');
                }

                $services = Service::whereIn('id', $serviceIds)->get();
                $appointment->fill([
                    'full_name' => $validated['full_name'],
                    'contact_number' => $validated['contact_number'],
                    'email' => $validated['email'] ?? null,
                    'party_size' => $validated['party_size'],
                    'service_id' => $serviceIds->first(),
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                ]);

                $appointment->price_at_booking = $services->sum('price') * $validated['party_size'];

                $appointment->client_id = $this->resolveClient($validated)->id;

                $appointment->save();
                $this->syncAppointmentServices($appointment, $services);
                if (! $appointment->invoice) {
                    $this->syncAppointmentParticipants($appointment, $validated, $services);
                }
                $this->syncPackageConsumption($appointment, $oldStatus);
                $this->syncAssignedStaff($appointment, $validated['status'] === 'cancelled' ? [] : ($validated['assigned_staff_ids'] ?? []));

                return $oldStatus !== 'completed' && $appointment->status === 'completed'
                    ? $appointment->fresh(['service', 'services'])
                    : null;
            });

            if ($completedAppointment) {
                app(AppointmentCompletionNotifier::class)->notify($completedAppointment);
            }

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully.',
            ]);
        } catch (\RuntimeException $e) {
            $messages = [
                'DAILY_CONFIRMED_LIMIT_REACHED' => 'This date already has 10 confirmed clients.',
                'PAID_SERVICE_CHANGE_BLOCKED' => 'This appointment already has an invoice. Service cannot be changed after payment.',
                'PACKAGE_SERVICE_CHANGE_BLOCKED' => 'The service cannot be changed because this appointment belongs to a package.',
                'PACKAGE_SESSIONS_EXHAUSTED' => 'This package has no remaining sessions.',
                'CONSENT_REQUIRED' => 'Client consent must be signed at the store before completing this appointment.',
            ];

            return response()->json([
                'success' => false,
                'message' => $messages[$e->getMessage()] ?? 'Failed to update appointment.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to update appointment', [
                'appointment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment. Please try again.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS (ADMIN / MANAGEMENT / RECEPTION ONLY)
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, $id)
    {
        if (! in_array(Auth::user()->role, ['admin', 'management', 'reception'])) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'assigned_staff_ids' => 'nullable|array',
            'assigned_staff_ids.*' => 'integer|distinct|exists:users,id',
        ]);

        if (! $this->areStaffMembers($request->input('assigned_staff_ids', []))) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            [$appointment, $shouldNotifyCompletion] = DB::transaction(function () use ($request, $id) {

                $appointment = Appointment::with(['services', 'consentForm'])->findOrFail($id);
                $oldStatus = $appointment->status;

                if ($request->status === 'completed' && $appointment->services->contains('requires_consent', true) && ! $appointment->consentForm) {
                    throw new \RuntimeException('CONSENT_REQUIRED');
                }

                if (
                    $request->status === 'confirmed' &&
                    $oldStatus !== 'confirmed' &&
                    $this->confirmedCountForDate($appointment->date, $appointment->id) >= self::DAILY_CONFIRMED_LIMIT
                ) {
                    throw new \RuntimeException('DAILY_CONFIRMED_LIMIT_REACHED');
                }

                $appointment->status = $request->status;

                /*
                |--------------------------------------------------------------
                | ASSIGN / UNASSIGN STAFF
                |--------------------------------------------------------------
                */
                $appointment->save();
                $this->syncPackageConsumption($appointment, $oldStatus);
                $this->syncAssignedStaff(
                    $appointment,
                    $request->status === 'cancelled' ? [] : $request->input('assigned_staff_ids', [])
                );

                return [
                    $appointment,
                    $oldStatus !== 'completed' && $appointment->status === 'completed',
                ];
            });

            if ($shouldNotifyCompletion) {
                app(AppointmentCompletionNotifier::class)->notify($appointment->fresh(['service']));
            }

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'DAILY_CONFIRMED_LIMIT_REACHED') {
                return response()->json([
                    'success' => false,
                    'message' => 'This date already has 10 confirmed clients. Ask the client to try another date or walk in.',
                ], 422);
            }

            if ($e->getMessage() === 'PACKAGE_SESSIONS_EXHAUSTED') {
                return response()->json([
                    'success' => false,
                    'message' => 'This package has no remaining sessions.',
                ], 422);
            }

            if ($e->getMessage() === 'CONSENT_REQUIRED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Client consent must be signed at the store before completing this appointment.',
                ], 422);
            }

            Log::error('Failed to update appointment status', [
                'appointment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to update appointment status', [
                'appointment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'status' => $appointment->status,
            'message' => 'Status updated successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorizeAppointmentAccess($appointment);

        return response()->json($appointment);
    }

    public function consentSignature($id)
    {
        $appointment = Appointment::with('consentForm')->findOrFail($id);
        $this->authorizeAppointmentAccess($appointment);

        abort_unless($appointment->consentForm, 404);
        $path = $appointment->consentForm->signature_path;

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        // Temporary compatibility for signatures created before private storage was introduced.
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function storeConsent(Request $request, $id)
    {
        $validated = $request->validate([
            'consent_accepted' => 'accepted',
            'consent_signature' => 'required|string|max:3000000',
        ]);
        $appointment = Appointment::with(['services', 'consentForm'])->findOrFail($id);
        $this->authorizeAppointmentAccess($appointment);
        if ($appointment->consentForm) {
            return response()->json(['success' => false, 'message' => 'Consent has already been signed.'], 422);
        }
        $service = $appointment->services->firstWhere('requires_consent', true);
        if (! $service) {
            return response()->json(['success' => false, 'message' => 'This appointment does not require consent.'], 422);
        }

        $this->storeConsentIfRequired($appointment, $service, $validated['consent_signature']);

        return response()->json(['success' => true, 'message' => 'Consent saved successfully.']);
    }

    public function searchClients(Request $request)
    {
        $search = trim(str_replace(['%', '_'], '', (string) $request->query('q')));
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

            return response()->json(
            Client::query()
                ->with(['servicePackages' => fn ($query) => $query
                    ->where('status', 'active')
                    ->whereColumn('used_sessions', '<', 'total_sessions')
                    ->with('service')])
                ->withCount(['appointments' => function ($query) {
                    $query->whereColumn('appointments.full_name', 'clients.full_name');
                }])
                ->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderByDesc('appointments_count')
                ->orderBy('full_name')
                ->limit(8)
                ->get(['id', 'full_name', 'contact_number', 'email'])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WALK-IN BOOKING
    |--------------------------------------------------------------------------
    */
    public function storeWalkIn(Request $request)
    {
        if (! in_array(Auth::user()->role, ['admin', 'management', 'staff', 'reception'])) {
            abort(403);
        }

        $request->merge([
            'contact_number' => preg_replace('/\s+/', '', (string) $request->input('contact_number')),
            'service_ids' => $request->input('service_ids', $request->filled('service_id') ? [$request->input('service_id')] : []),
            'party_size' => $request->input('party_size', 1),
        ]);

        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1|max:50',
            'client_id' => 'nullable|integer|exists:clients,id',
            'service_package_id' => 'nullable|integer|exists:service_packages,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'integer|distinct|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'assigned_staff_ids' => 'nullable|array',
            'assigned_staff_ids.*' => 'integer|distinct|exists:users,id',
            'consent_accepted' => 'nullable|boolean',
            'consent_signature' => 'nullable|string|max:3000000',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
            'date.after_or_equal' => 'Please choose today or a future date.',
        ]);

        $services = Service::whereIn('id', $validated['service_ids'])->get();
        $service = $services->first();
        $consentService = $services->firstWhere('requires_consent', true);
        if (! empty($validated['service_package_id']) && ($services->count() > 1 || $validated['party_size'] > 1)) {
            return response()->json(['success' => false, 'message' => 'A service package can only be used for one client and one service.'], 422);
        }
        if (empty($validated['service_package_id']) && $services->contains(fn ($item) => ! $item->is_active)) {
            return response()->json(['success' => false, 'message' => 'The selected service is not available.'], 422);
        }
        if ($services->contains(fn ($item) => (int) $item->session_count > 1)
            && ($services->count() > 1 || $validated['party_size'] > 1)) {
            return response()->json(['success' => false, 'message' => 'A multi-session package must be booked alone for one client.'], 422);
        }
        if ($consentService && empty($validated['consent_accepted'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please ask the client to read and accept the consent form for this service.',
            ], 422);
        }

        if ($consentService && empty($validated['consent_signature'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please ask the client to sign the consent form for this service.',
            ], 422);
        }

        if (! $this->areStaffMembers($validated['assigned_staff_ids'] ?? [])) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            $appointment = DB::transaction(function () use ($validated, $service, $services, $consentService) {
                $client = $this->resolveClient(
                    $validated,
                    $validated['client_id'] ?? null,
                    empty($validated['client_id'])
                );
                $appointment = Appointment::create([
                    'full_name' => $validated['full_name'],
                    'contact_number' => $validated['contact_number'],
                    'email' => $validated['email'] ?? null,
                    'party_size' => $validated['party_size'],
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'price_at_booking' => $services->sum('price') * $validated['party_size'],
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'status' => 'confirmed',
                    'booking_type' => 'walk-in',
                ]);

                $this->syncAppointmentServices($appointment, $services);
                $this->syncAppointmentParticipants($appointment, $validated, $services);

                $this->attachServicePackage(
                    $appointment,
                    $service,
                    $client,
                    isset($validated['service_package_id']) ? (int) $validated['service_package_id'] : null
                );

                $this->syncAssignedStaff($appointment, $validated['assigned_staff_ids'] ?? []);

                if ($consentService) {
                    $this->storeConsentIfRequired($appointment, $consentService, $validated['consent_signature'] ?? null);
                }

                return $appointment;
            });

            return response()->json([
                'success' => true,
                'message' => 'Walk-in appointment created successfully.',
                'appointment' => $appointment,
            ]);

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'INVALID_SERVICE_PACKAGE') {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected package is unavailable or does not belong to this client and service.',
                ], 422);
            }

            if ($e->getMessage() === 'DAILY_CONFIRMED_LIMIT_REACHED') {
                return response()->json([
                    'success' => false,
                    'message' => 'This date already has 10 confirmed clients. Please choose another date.',
                ], 422);
            }

            Log::error('Failed to create walk-in appointment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create walk-in appointment. Please try again.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to create walk-in appointment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create walk-in appointment. Please try again.',
            ], 500);
        }
    }

    private function confirmedCountForDate(string $date, ?int $excludeAppointmentId = null): int
    {
        return Appointment::query()
            ->whereDate('date', $date)
            ->where('status', 'confirmed')
            ->where(function ($query) {
                $query->where('booking_type', 'online')
                    ->orWhereNull('booking_type');
            })
            ->when($excludeAppointmentId, fn ($query) => $query->where('id', '!=', $excludeAppointmentId))
            ->count();
    }

    private function isStaffMember(int $userId): bool
    {
        return User::where('id', $userId)->where('role', 'staff')->exists();
    }

    private function areStaffMembers(array $userIds): bool
    {
        if ($userIds === []) {
            return true;
        }

        return User::whereIn('id', $userIds)->where('role', 'staff')->count() === count($userIds);
    }

    private function resolveClient(array $data, ?int $clientId = null, bool $forceNew = false): Client
    {
        $contact = $this->normalizeContactNumber($data['contact_number']);
        $user = User::query()
            ->where('role', 'customer')
            ->whereIn('phone', array_unique([$contact, $data['contact_number']]))
            ->where('name', $data['full_name'])
            ->first();

        $client = $forceNew
            ? new Client
            : (($clientId ? Client::findOrFail($clientId) : null)
                ?? ($user?->client)
                ?? Client::where('contact_number', $contact)
                    ->where('full_name', $data['full_name'])
                    ->first()
                ?? new Client);

        $client->fill([
            'user_id' => $user?->id ?? $client->user_id,
            'full_name' => $data['full_name'],
            'contact_number' => $contact,
            'email' => $data['email'] ?? $client->email,
        ])->save();

        return $client;
    }

    private function normalizeContactNumber(string $contact): string
    {
        $contact = preg_replace('/\s+/', '', $contact);

        return str_starts_with($contact, '+63') ? '0'.substr($contact, 3) : $contact;
    }

    private function attachServicePackage(Appointment $appointment, Service $service, Client $client, ?int $packageId = null): void
    {
        if ($packageId) {
            $package = ServicePackage::lockForUpdate()->findOrFail($packageId);
            if ($package->client_id !== $client->id
                || $package->service_id !== $service->id
                || $package->status !== 'active'
                || $package->available_sessions < 1) {
                throw new \RuntimeException('INVALID_SERVICE_PACKAGE');
            }

            $appointment->update([
                'service_package_id' => $package->id,
                'price_at_booking' => 0,
            ]);
            return;
        }

        if ((int) $service->session_count <= 1) {
            return;
        }

        $package = ServicePackage::create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'total_sessions' => $service->session_count,
            'used_sessions' => 0,
            'total_price' => $service->price,
            'status' => 'active',
        ]);

        $appointment->update(['service_package_id' => $package->id]);
    }

    private function syncPackageConsumption(Appointment $appointment, string $oldStatus): void
    {
        if (! $appointment->service_package_id) {
            return;
        }

        $package = ServicePackage::lockForUpdate()->findOrFail($appointment->service_package_id);

        if ($oldStatus !== 'completed' && $appointment->status === 'completed' && ! $appointment->package_session_consumed) {
            if ($package->remaining_sessions < 1) {
                throw new \RuntimeException('PACKAGE_SESSIONS_EXHAUSTED');
            }
            $package->increment('used_sessions');
            $appointment->update(['package_session_consumed' => true]);
        } elseif ($oldStatus === 'completed' && $appointment->status !== 'completed' && $appointment->package_session_consumed) {
            $package->decrement('used_sessions');
            $appointment->update(['package_session_consumed' => false]);
        }

        $package->refresh();
        $package->update(['status' => $package->remaining_sessions === 0 ? 'completed' : 'active']);
    }

    private function syncAssignedStaff(Appointment $appointment, array $userIds): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $appointment->assignedStaffMembers()->sync($userIds);
    }

    private function syncAppointmentServices(Appointment $appointment, $services): void
    {
        $appointment->services()->sync($services->mapWithKeys(fn ($service) => [
            $service->id => ['price_at_booking' => $service->price],
        ])->all());
    }

    private function participantBookingTotal(array $validated, $services): float
    {
        $prices = $services->pluck('price', 'id');
        if (! empty($validated['participants'])) {
            return (float) collect($validated['participants'])->sum(fn ($participant) =>
                collect($participant['service_ids'])->unique()->sum(fn ($id) => (float) ($prices[(int) $id] ?? 0))
            );
        }

        return (float) $services->sum('price') * max(1, (int) $validated['party_size']);
    }

    private function syncAppointmentParticipants(Appointment $appointment, array $validated, $services): void
    {
        $participants = ! empty($validated['participants'])
            ? collect($validated['participants'])->values()
            : collect(range(1, max(1, (int) $validated['party_size'])))->map(fn ($position) => [
                'name' => $position === 1 ? $validated['full_name'] : null,
                'service_ids' => $services->pluck('id')->all(),
            ]);
        $prices = $services->pluck('price', 'id');

        $appointment->participants()->delete();
        foreach ($participants as $index => $data) {
            $participant = $appointment->participants()->create([
                'name' => filled($data['name'] ?? null) ? $data['name'] : ($index === 0 ? $validated['full_name'] : null),
                'position' => $index + 1,
            ]);
            $participant->services()->sync(collect($data['service_ids'])->unique()->mapWithKeys(fn ($id) => [
                (int) $id => ['price_at_booking' => $prices[(int) $id]],
            ])->all());
        }
    }

    private function storeConsentIfRequired(Appointment $appointment, Service $service, ?string $signatureData): void
    {
        if (! $service->requires_consent) {
            return;
        }

        if (! $signatureData || ! preg_match('/^data:image\/png;base64,/', $signatureData)) {
            throw new \InvalidArgumentException('A valid signature is required.');
        }

        $image = base64_decode(preg_replace('/^data:image\/png;base64,/', '', $signatureData), true);
        if ($image === false) {
            throw new \InvalidArgumentException('The signature could not be saved.');
        }

        if (strlen($image) > 2 * 1024 * 1024) {
            throw new \InvalidArgumentException('The signature image is too large.');
        }

        $path = 'consent-signatures/appointment-'.$appointment->id.'-'.now()->format('YmdHis').'.png';
        Storage::disk('local')->put($path, $image);

        ConsentForm::create([
            'appointment_id' => $appointment->id,
            'service_id' => $service->id,
            'full_name' => $appointment->full_name,
            'contact_number' => $appointment->contact_number,
            'email' => $appointment->email,
            'consent_text' => $this->consentText($service),
            'signature_path' => $path,
            'signed_at' => now(),
        ]);
    }

    private function consentText(Service $service): string
    {
        return implode("\n", [
            "Consent for {$service->name}:",
            'I voluntarily request the selected service.',
            'I have disclosed relevant allergies, health conditions, medications, pregnancy, or skin and nail concerns.',
            'I understand the service has been explained to me and I may ask questions or stop the service at any time.',
            'I agree to follow the aftercare guidance provided by Martinis and Manicures.',
            'I have read and agree to these statements.',
        ]);
    }

    private function authorizeAppointmentAccess(Appointment $appointment): void
    {
        $user = Auth::user();
        $isPrivileged = in_array($user->role, ['admin', 'management', 'reception'])
            || ($user->role === 'staff' && $appointment->assignedStaffMembers()->where('users.id', $user->id)->exists());
        $isOwner = $user->role === 'customer'
            && $appointment->client()->where('user_id', $user->id)->exists();

        abort_unless($isPrivileged || $isOwner, 403);
    }
}
