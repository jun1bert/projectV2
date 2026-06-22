<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\StaffCommission;
use App\Models\CommissionRule;

class CommissionService
{
    /**
     * CREATE OR UPDATE (SAFE IDENTITY RULE)
     */
   public function createPendingCommission(Appointment $appointment)
{
    if (!$appointment->assigned_to || !$appointment->service) {
        return;
    }

    // ONE COMMISSION ONLY
    $commission = StaffCommission::firstOrNew([
        'appointment_id' => $appointment->id,
    ]);

    // If already paid, lock it permanently
    if ($commission->status === 'paid') {
        return;
    }

    // ================= RULE RESOLUTION (MOVE HERE) =================
    $rule = $this->resolveRule($appointment);
    $rate = $rule?->value ?? 10;

    $amount = $this->calculate($appointment);

    $commission->fill([
        'staff_id' => $appointment->assigned_to,
        'service_id' => $appointment->service_id,
        'service_amount' => $appointment->service->price ?? 0,
        'commission_rate' => $rate,
        'commission_amount' => $amount,
        'status' => 'pending',
        'earned_at' => null,
    ]);

    $commission->save();
}

    /**
     * MARK AS PAID (FINAL ACTION ONLY)
     */
    public function markAsPaid(Appointment $appointment)
    {
        $commission = StaffCommission::where('appointment_id', $appointment->id)->first();

        if (!$commission) {
            return false;
        }

        $commission->update([
            'status' => 'paid',
            'earned_at' => now(),
        ]);

        return true;
    }

private function calculate(Appointment $appointment)
{
    $rule = $this->resolveRule($appointment);

    $price = $appointment->service->price ?? 0;

    if (!$rule) {
        return $price * 0.10;
    }

    if ($rule->type === 'percentage') {
        return $price * ($rule->value / 100);
    }

    if ($rule->type === 'fixed') {
        return $rule->value;
    }

    return 0;
}

    private function resolveRule(Appointment $appointment)
{
    return CommissionRule::where('is_active', true)
        ->where(function ($q) use ($appointment) {

            $q->where(function ($q2) use ($appointment) {
                $q2->where('staff_id', $appointment->assigned_to)
                   ->where('service_id', $appointment->service_id);
            })

            ->orWhere(function ($q2) use ($appointment) {
                $q2->where('staff_id', $appointment->assigned_to)
                   ->whereNull('service_id');
            })

            ->orWhere(function ($q2) use ($appointment) {
                $q2->whereNull('staff_id')
                   ->where('service_id', $appointment->service_id);
            })

            ->orWhere(function ($q2) {
                $q2->whereNull('staff_id')
                   ->whereNull('service_id');
            });

        })
        ->orderByDesc('priority')
        ->first();
}
}