<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:cash,gcash',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $appointment = Appointment::with(['service', 'assignedStaffMembers'])
                ->lockForUpdate()
                ->findOrFail($id);

            $user = Auth::user();
            if ($user->role === 'staff' && ! $appointment->assignedStaffMembers->contains('id', $user->id)) {
                abort(403);
            }

            if ($appointment->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment can only be recorded for a completed appointment.',
                ], 422);
            }

            if ($appointment->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this appointment.',
                ]);
            }

            $servicePrice = $appointment->service->price ?? 0;

            $invoice = Invoice::create([
                'appointment_id' => $appointment->id,
                'service_total' => $servicePrice,
                'grand_total' => $servicePrice,
                'payment_method' => $request->method,
                'status' => 'paid',
            ]);

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
            ]);
        });
    }
}
