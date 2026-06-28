<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:cash,gcash',
            'payment_type' => 'nullable|in:full,partial',
            'amount' => 'required_if:payment_type,partial|nullable|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $appointment = Appointment::with(['service', 'servicePackage', 'assignedStaffMembers'])
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

            $invoice = $appointment->billing_invoice;
            $servicePrice = $appointment->servicePackage?->total_price
                ?? $appointment->price_at_booking
                ?? $appointment->service->price
                ?? 0;

            if (! $invoice) {
                $invoice = Invoice::create([
                    'appointment_id' => $appointment->id,
                    'service_total' => $servicePrice,
                    'grand_total' => $servicePrice,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                ]);
            }

            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $balance = $invoice->balance;
            if ($balance <= 0) {
                return response()->json(['success' => false, 'message' => 'This invoice is already fully paid.'], 422);
            }

            $amount = ($request->input('payment_type', 'full') === 'partial')
                ? (float) $request->amount
                : $balance;

            if ($amount > $balance) {
                return response()->json(['success' => false, 'message' => 'Payment cannot exceed the remaining balance.'], 422);
            }

            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => $request->method,
                'notes' => $request->notes,
                'received_by' => $user->id,
            ]);

            $invoice->amount_paid = (float) $invoice->amount_paid + $amount;
            $invoice->payment_method = $request->method;
            $invoice->status = $invoice->amount_paid >= (float) $invoice->grand_total ? 'paid' : 'partially_paid';
            $invoice->save();

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'status' => $invoice->status,
                'balance' => $invoice->balance,
            ]);
        });
    }
}
