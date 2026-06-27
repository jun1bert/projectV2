<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\ConsentForm;
use App\Models\User;
use App\Models\Service;
use App\Services\AppointmentCompletionNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'full_name'      => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email'          => 'nullable|email|max:255',
            'service_id'     => 'required|exists:services,id',
            'date'           => 'required|date|after_or_equal:today',
            'time'           => 'required|date_format:H:i',
            'notes'          => 'nullable|string|max:1000',
            'consent_accepted' => 'nullable|boolean',
            'consent_signature' => 'nullable|string',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
            'date.after_or_equal'  => 'Please choose today or a future date.',
        ]);

        $service = Service::findOrFail($validated['service_id']);
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

        if (!in_array($validated['time'], self::ONLINE_TIME_SLOTS, true)) {
            return back()
                ->withInput()
                ->withErrors(['time' => 'Please choose one of the available online appointment times.']);
        }

        DB::transaction(function () use ($validated, $service) {
            $appointment = Appointment::create([
                'full_name'      => $validated['full_name'],
                'contact_number' => $validated['contact_number'],
                'email'          => $validated['email'] ?? null,
                'service_id'     => $validated['service_id'],
                'date'           => $validated['date'],
                'time'           => $validated['time'],
                'notes'          => $validated['notes'] ?? null,
                'status'         => 'pending',
            ]);

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
        $user  = Auth::user();
        $query = Appointment::query();

        if (in_array($user->role, ['admin', 'management', 'reception'])) {
            $appointments = $query->latest()->get();
        } elseif ($user->role === 'staff') {
            $appointments = $query->where('assigned_to', $user->id)->latest()->get();
        } else {
            $appointments = $query->where('contact_number', $user->phone)->latest()->get();
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
        if (!in_array(Auth::user()->role, ['admin', 'management', 'staff', 'reception'])) {
            abort(403);
        }

        $appointments = Appointment::with(['service', 'assignedStaff', 'invoice', 'consentForm'])->latest()->paginate(8);
        $staff        = User::where('role', 'staff')->orderBy('name')->get();
        $services     = Service::all();
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
                    'signature_url' => asset('storage/' . $appointment->consentForm->signature_path),
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
                'service_price' => (float) ($appointment->service->price ?? 0),
                'date' => $appointment->date,
                'time' => substr((string) $appointment->time, 0, 5),
                'notes' => $appointment->notes,
                'status' => $appointment->status,
                'booking_type' => $appointment->booking_type ?? 'online',
                'assigned_to' => $appointment->assigned_to,
                'assigned_staff' => $appointment->assignedStaff->name ?? 'Unassigned',
                'payment_status' => $appointment->payment_status ?? 'unpaid',
                'has_invoice' => (bool) $appointment->invoice,
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
        if (!in_array(Auth::user()->role, ['admin', 'management', 'reception'])) {
            abort(403);
        }

        $request->merge([
            'contact_number' => preg_replace('/\s+/', '', (string) $request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'full_name'      => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email'          => 'nullable|email|max:255',
            'service_id'     => 'required|exists:services,id',
            'date'           => 'required|date',
            'time'           => 'required|date_format:H:i',
            'notes'          => 'nullable|string|max:1000',
            'status'         => 'required|in:pending,confirmed,cancelled,completed',
            'assigned_to'    => 'nullable|exists:users,id',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
        ]);

        if ($request->filled('assigned_to') && !$this->isStaffMember((int) $request->assigned_to)) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            $completedAppointment = DB::transaction(function () use ($id, $validated, $request) {
                $appointment = Appointment::with('invoice')->findOrFail($id);
                $oldStatus = $appointment->status;

                if ($appointment->invoice && (int) $appointment->service_id !== (int) $validated['service_id']) {
                    throw new \RuntimeException('PAID_SERVICE_CHANGE_BLOCKED');
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
                    'assigned_to' => $validated['status'] === 'cancelled' ? null : ($request->assigned_to ?: null),
                ]);

                $appointment->save();

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
        if (!in_array(Auth::user()->role, ['admin', 'management', 'reception'])) {
            abort(403);
        }

        $request->validate([
            'status'      => 'required|in:pending,confirmed,cancelled,completed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($request->filled('assigned_to') && !$this->isStaffMember((int) $request->assigned_to)) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            [$appointment, $shouldNotifyCompletion] = DB::transaction(function () use ($request, $id) {

                $appointment = Appointment::findOrFail($id);
                $oldStatus   = $appointment->status;

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
                if ($request->status === 'confirmed' && $request->filled('assigned_to')) {
                    $appointment->assigned_to = $request->assigned_to;
                }

                if ($request->status === 'cancelled') {
                    $appointment->assigned_to = null;
                }

                $appointment->save();

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

            Log::error('Failed to update appointment status', [
                'appointment_id' => $id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to update appointment status', [
                'appointment_id' => $id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'status'  => $appointment->status,
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
        $user        = Auth::user();
        $appointment = Appointment::findOrFail($id);

        $isStaffOrAdmin = in_array($user->role, ['admin', 'management', 'reception'])
            || ($user->role === 'staff' && $appointment->assigned_to === $user->id);

        $isOwner = $appointment->contact_number === $user->phone;

        if (!$isStaffOrAdmin && !$isOwner) {
            abort(403);
        }

        return response()->json($appointment);
    }

    /*
    |--------------------------------------------------------------------------
    | WALK-IN BOOKING
    |--------------------------------------------------------------------------
    */
    public function storeWalkIn(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'management', 'staff', 'reception'])) {
            abort(403);
        }

        $request->merge([
            'contact_number' => preg_replace('/\s+/', '', (string) $request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'full_name'      => 'required|string|min:2|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^(\+63|0)9\d{9}$/'],
            'email'          => 'nullable|email|max:255',
            'service_id'     => 'required|exists:services,id',
            'date'           => 'required|date|after_or_equal:today',
            'time'           => 'required|date_format:H:i',
            'assigned_to'    => 'nullable|exists:users,id',
            'consent_accepted' => 'nullable|boolean',
            'consent_signature' => 'nullable|string',
        ], [
            'contact_number.regex' => 'Please enter a valid PH mobile number, for example 09171234567.',
            'date.after_or_equal'  => 'Please choose today or a future date.',
        ]);

        $service = Service::findOrFail($validated['service_id']);
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

        if ($request->filled('assigned_to') && !$this->isStaffMember((int) $request->assigned_to)) {
            return response()->json(['success' => false, 'message' => 'Selected user is not a staff member.'], 422);
        }

        try {
            $appointment = DB::transaction(function () use ($validated, $request, $service) {
                $appointment = Appointment::create([
                    'full_name'      => $validated['full_name'],
                    'contact_number' => $validated['contact_number'],
                    'email'          => $validated['email'] ?? null,
                    'service_id'     => $validated['service_id'],
                    'date'           => $validated['date'],
                    'time'           => $validated['time'],
                    'status'         => 'confirmed',
                    'booking_type'   => 'walk-in',
                    'assigned_to'    => $request->assigned_to,
                ]);

                $this->storeConsentIfRequired($appointment, $service, $validated['consent_signature'] ?? null);

                return $appointment;
            });

            return response()->json([
                'success'     => true,
                'message'     => 'Walk-in appointment created successfully.',
                'appointment' => $appointment,
            ]);

        } catch (\RuntimeException $e) {
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

    private function storeConsentIfRequired(Appointment $appointment, Service $service, ?string $signatureData): void
    {
        if (!$service->requires_consent) {
            return;
        }

        if (!$signatureData || !preg_match('/^data:image\/png;base64,/', $signatureData)) {
            throw new \InvalidArgumentException('A valid signature is required.');
        }

        $image = base64_decode(preg_replace('/^data:image\/png;base64,/', '', $signatureData), true);
        if ($image === false) {
            throw new \InvalidArgumentException('The signature could not be saved.');
        }

        $path = 'consent-signatures/appointment-' . $appointment->id . '-' . now()->format('YmdHis') . '.png';
        Storage::disk('public')->put($path, $image);

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
}
