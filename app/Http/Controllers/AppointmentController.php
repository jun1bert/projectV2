<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CommissionService;

class AppointmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ONLINE BOOKING (CUSTOMER)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'contact_number' => 'required',
            'service_id' => 'required|exists:services,id',
            'date' => 'required',
            'time' => 'required',
        ]);

        Appointment::create([
            'full_name' => $request->full_name,
            'contact_number' => $request->contact_number,
            'service_id' => $request->service_id,
            'date' => $request->date,
            'time' => $request->time,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

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

        if (in_array($user->role, ['admin', 'management'])) {
            $appointments = $query->latest()->get();
        }
        elseif ($user->role === 'staff') {
            $appointments = $query->where('assigned_to', $user->id)->latest()->get();
        }
        else {
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
        if (!in_array(Auth::user()->role, ['admin', 'management', 'staff'])) {
            abort(403);
        }

        $appointments = Appointment::with(['service', 'invoice'])->latest()->paginate(8);
        $staff = User::where('role', 'staff')->get();
        $services = Service::all();

       return response()
        ->view('appointments.index', compact('appointments', 'staff', 'services'))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS (ADMIN / MANAGEMENT ONLY)
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'management'])) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,rejected,completed',
            // Must actually be a staff member, not just any existing user
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($request->filled('assigned_to')) {
            $isStaff = User::where('id', $request->assigned_to)
                ->where('role', 'staff')
                ->exists();

            if (!$isStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a staff member.',
                ], 422);
            }
        }

        try {
            $appointment = DB::transaction(function () use ($request, $id) {

                $appointment = Appointment::findOrFail($id);
                $oldStatus = $appointment->status;

                $appointment->status = $request->status;

                /*
                |--------------------------------------------------------------------------
                | ASSIGN / UNASSIGN STAFF
                |--------------------------------------------------------------------------
                */
                if ($request->status === 'confirmed') {
                    // Only touch assigned_to if the request actually sent one,
                    // so re-confirming without resending assigned_to doesn't
                    // silently wipe an existing assignment.
                    if ($request->filled('assigned_to')) {
                        $appointment->assigned_to = $request->assigned_to;
                    }
                }

                if ($request->status === 'rejected') {
                    $appointment->assigned_to = null;
                }

                $appointment->save();

                /*
                |--------------------------------------------------------------------------
                | COMPLETED HOOK (RECEIPT STARTS HERE)
                |--------------------------------------------------------------------------
                */
                if ($request->status === 'completed') {
                    // we will build:
                    // - items used
                    // - inventory deduction
                    // - receipt generation
                }

                /*
                |--------------------------------------------------------------------------
                | COMMISSION LOGIC (SINGLE SOURCE OF TRUTH)
                |--------------------------------------------------------------------------
                */
                $commissionService = app(CommissionService::class);

                // CREATE commission ONLY on FIRST confirmation
                if (
                    $request->status === 'confirmed' &&
                    $oldStatus !== 'confirmed' &&
                    $appointment->assigned_to
                ) {
                    $commissionService->createPendingCommission($appointment);
                }

                return $appointment;
            });
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
            'message' => 'Status updated successfully.'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $user = Auth::user();
        $appointment = Appointment::findOrFail($id);

        $isStaffOrAdmin = in_array($user->role, ['admin', 'management'])
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
        if (!in_array(Auth::user()->role, ['admin', 'management', 'staff'])) {
            abort(403);
        }

        $validated = $request->validate([
            'full_name' => 'required',
            'contact_number' => 'required',
            'service_id' => 'required|exists:services,id',
            'date' => 'required',
            'time' => 'required',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($request->filled('assigned_to')) {
            $isStaff = User::where('id', $request->assigned_to)
                ->where('role', 'staff')
                ->exists();

            if (!$isStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a staff member.',
                ], 422);
            }
        }

        try {
            $appointment = DB::transaction(function () use ($validated, $request) {

                $appointment = Appointment::create([
                    'full_name' => $validated['full_name'],
                    'contact_number' => $validated['contact_number'],
                    'service_id' => $validated['service_id'],
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'status' => 'confirmed',
                    'booking_type' => 'walk-in',
                    'assigned_to' => $request->assigned_to,
                ]);

                /*
                |--------------------------------------------------------------------------
                | COMMISSION (WALK-IN SAFETY CHECK)
                |--------------------------------------------------------------------------
                */
                if (
                    $appointment->assigned_to &&
                    $appointment->status === 'confirmed'
                ) {
                    app(CommissionService::class)
                        ->createPendingCommission($appointment);
                }

                return $appointment;
            });

            return response()->json([
                'success' => true,
                'message' => 'Walk-in appointment created successfully.',
                'appointment' => $appointment
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create walk-in appointment', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create walk-in appointment. Please try again.'
            ], 500);
        }
    }
}