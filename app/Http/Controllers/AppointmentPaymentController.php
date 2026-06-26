<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $appointment = Appointment::with('service')->findOrFail($id);

            if ($appointment->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already exists for this appointment.',
                ]);
            }

            $servicePrice = $appointment->service->price ?? 0;

            $invoice = Invoice::create([
                'appointment_id' => $appointment->id,
                'service_total'  => $servicePrice,
                'grand_total'    => $servicePrice,
                'payment_method' => $request->method,
                'status'         => 'paid',
            ]);

            $appointment->update(['payment_status' => 'paid']);

            return response()->json([
                'success'    => true,
                'invoice_id' => $invoice->id,
            ]);
        });
    }
}
