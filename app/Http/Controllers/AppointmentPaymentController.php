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
            'payment_type' => 'nullable|in:full,partial,per_client',
            'amount' => 'required_if:payment_type,partial|nullable|numeric|min:0.01',
            'client_count' => 'required_if:payment_type,per_client|nullable|integer|min:1',
            'participant_ids' => 'nullable|array|min:1',
            'participant_ids.*' => 'integer|distinct',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $appointment = Appointment::with(['service', 'services', 'participants.services', 'participants.payments', 'servicePackage', 'assignedStaffMembers'])
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
                ?? $appointment->services_total;

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

            $paymentType = $request->input('payment_type', 'full');
            $clientCount = null;
            $participantAmounts = collect();
            if ($paymentType === 'per_client') {
                $available = $appointment->participants->filter(fn ($participant) =>
                    $participant->total - (float) $participant->payments->sum(fn ($payment) => (float) $payment->pivot->amount) > 0.009
                );
                $selectedIds = collect($request->input('participant_ids', []))->map(fn ($id) => (int) $id);
                $selected = $selectedIds->isNotEmpty()
                    ? $available->whereIn('id', $selectedIds)
                    : $available->take((int) $request->client_count);
                if ($selected->count() !== ($selectedIds->isNotEmpty() ? $selectedIds->count() : (int) $request->client_count)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more selected clients are invalid or already fully paid.',
                    ], 422);
                }
                $participantAmounts = $selected->mapWithKeys(function ($participant) {
                    $paid = (float) $participant->payments->sum(fn ($payment) => (float) $payment->pivot->amount);
                    return [$participant->id => max(0, $participant->total - $paid)];
                });
                $clientCount = $selected->count();
                $amount = (float) $participantAmounts->sum();
            } else {
                $amount = $paymentType === 'partial' ? (float) $request->amount : $balance;
            }

            if ($amount > $balance) {
                return response()->json(['success' => false, 'message' => 'Payment cannot exceed the remaining balance.'], 422);
            }

            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_scope' => $paymentType === 'per_client' ? 'per_client' : ($paymentType === 'partial' ? 'custom' : 'whole'),
                'client_count' => $clientCount,
                'payment_method' => $request->method,
                'notes' => $request->notes,
                'received_by' => $user->id,
            ]);
            if ($participantAmounts->isNotEmpty()) {
                $payment->participants()->attach($participantAmounts->map(fn ($amount) => ['amount' => $amount])->all());
            }

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
