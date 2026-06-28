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
        $request->merge([
            'contact_number' => preg_replace('/\s+/', '', (string) $request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email' => 'nullable|email|max:255',
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            'consent_accepted' => 'nullable|boolean',
            'consent_signature' => 'nullable|string|max:3000000',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
            'date.after_or_equal' => 'Please choose today or a future date.',
        ]);

        $service = empty($validated['service_package_id'])
            ? Service::findOrFail($validated['service_id'])
            : Service::withTrashed()->findOrFail($validated['service_id']);
        if ($service->requires_consent && empty($validated['consent_accepted'])) {
            return back()
                ->withInput()
                ->withErrors(['consent_accepted' => 'Please read and accept the consent form for this service.']);
        }

        if ($service->requires_consent && empty($validated['consent_signature'])) {
            return back()
                ->withInput()
                ->withErrors(['consent_signature' => 'Please sign the consent form for this service.']);
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

        DB::transaction(function () use ($validated, $service) {
            $client = $this->resolveClient($validated);
            $appointment = Appointment::create([
                'full_name' => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['email'] ?? null,
                'client_id' => $client->id,
                'service_id' => $validated['service_id'],
                'price_at_booking' => $service->price,
                'date' => $validated['date'],
                'time' => $validated['time'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            $this->attachServicePackage($appointment, $service, $client);

            $this->storeConsentIfRequired($appointment, $service, $validated['consent_signature'] ?? null);
        });

        return redirect('/#book')
            ->with('success', 'Appointment submitted successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $user = Auth::user();
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

        $appointments = Appointment::with(['service', 'servicePackage', 'assignedStaffMembers', 'invoice', 'consentForm'])
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
                'service_id' => $appointment->service_id,
                'service_name' => $appointment->service->name ?? 'No service',
                'service_price' => (float) ($appointment->price_at_booking ?? $appointment->service->price ?? 0),
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
                'requires_consent' => (bool) ($appointment->service->requires_consent ?? false),
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
        ]);

        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email' => 'nullable|email|max:255',
            'service_id' => 'required|exists:services,id',
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
                $appointment = Appointment::with('invoice')->findOrFail($id);
                $oldStatus = $appointment->status;

                if ($appointment->invoice && (int) $appointment->service_id !== (int) $validated['service_id']) {
                    throw new \RuntimeException('PAID_SERVICE_CHANGE_BLOCKED');
                }

                if ($appointment->service_package_id && (int) $appointment->service_id !== (int) $validated['service_id']) {
                    throw new \RuntimeException('PACKAGE_SERVICE_CHANGE_BLOCKED');
                }

                if (
                    $validated['status'] === 'confirmed' &&
                    $appointment->status !== 'confirmed' &&
                    $this->confirmedCountForDate($validated['date'], $appointment->id) >= self::DAILY_CONFIRMED_LIMIT
                ) {
                    throw new \RuntimeException('DAILY_CONFIRMED_LIMIT_REACHED');
                }

                $appointment->fill([
                    'full_name' => $validated['full_name'],
                    'contact_number' => $validated['contact_number'],
                    'email' => $validated['email'] ?? null,
                    'service_id' => $validated['service_id'],
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                ]);

                if ((int) $appointment->getOriginal('service_id') !== (int) $validated['service_id']) {
                    $appointment->price_at_booking = Service::findOrFail($validated['service_id'])->price;
                }

                $appointment->client_id = $this->resolveClient($validated)->id;

                $appointment->save();
                $this->syncPackageConsumption($appointment, $oldStatus);
                $this->syncAssignedStaff($appointment, $validated['status'] === 'cancelled' ? [] : ($validated['assigned_staff_ids'] ?? []));

                return $oldStatus !== 'completed' && $appointment->status === 'completed'
                    ? $appointment->fresh(['service'])
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

                $appointment = Appointment::findOrFail($id);
                $oldStatus = $appointment->status;

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
        ]);

        $validated = $request->validate([
            'full_name' => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email' => 'nullable|email|max:255',
            'client_id' => 'nullable|integer|exists:clients,id',
            'service_package_id' => 'nullable|integer|exists:service_packages,id',
            'service_id' => ['required', 'integer', 'exists:services,id'],
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

        $service = Service::findOrFail($validated['service_id']);
        if (empty($validated['service_package_id']) && ! $service->is_active) {
            return response()->json(['success' => false, 'message' => 'The selected service is not available.'], 422);
        }
        if ($service->requires_consent && empty($validated['consent_accepted'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please ask the client to read and accept the consent form for this service.',
            ], 422);
        }

        if ($service->requires_consent && empty($validated['consent_signature'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please ask the client to sign the consent form for this service.',
            ], 422);
        }

        if (! $this->areStaffMembers($validated['assigned_staff_ids'] ?? [])) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            $appointment = DB::transaction(function () use ($validated, $service) {
                $client = $this->resolveClient(
                    $validated,
                    $validated['client_id'] ?? null,
                    empty($validated['client_id'])
                );
                $appointment = Appointment::create([
                    'full_name' => $validated['full_name'],
                    'contact_number' => $validated['contact_number'],
                    'email' => $validated['email'] ?? null,
                    'client_id' => $client->id,
                    'service_id' => $validated['service_id'],
                    'price_at_booking' => $service->price,
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'status' => 'confirmed',
                    'booking_type' => 'walk-in',
                ]);

                $this->attachServicePackage(
                    $appointment,
                    $service,
                    $client,
                    isset($validated['service_package_id']) ? (int) $validated['service_package_id'] : null
                );

                $this->syncAssignedStaff($appointment, $validated['assigned_staff_ids'] ?? []);

                $this->storeConsentIfRequired($appointment, $service, $validated['consent_signature'] ?? null);

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
