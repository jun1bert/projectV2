<?php

namespace App\Http\Controllers;

use App\Models\StaffCommission;
use Illuminate\Http\Request;

class StaffCommissionController extends Controller
{
    /**
     * GET COMMISSION DETAILS PER STAFF
     */
    public function details($staffId)
    {
        $commissions = StaffCommission::with(['service', 'appointment'])
            ->where('staff_id', $staffId)
            ->get();

        return response()->json([
            'total' => $commissions->sum('commission_amount'),
            'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'paid' => $commissions->where('status', 'paid')->sum('commission_amount'),

            'items' => $commissions->map(function ($c) {
                return [
                    'id' => $c->id, // 🔥 CRITICAL FIX
                    'service' => $c->service->name ?? 'N/A',
                    'date' => optional($c->appointment)->date,
                    'time' => optional($c->appointment)->time,
                    'amount' => $c->service_amount,
                    'commission' => $c->commission_amount,
                    'status' => $c->status,
                ];
            })
        ]);
    }

    /**
     * MARK AS PAID
     */
    public function markPaid($id)
    {
        $commission = StaffCommission::find($id);

        if (!$commission) {
            return response()->json([
                'success' => false,
                'message' => 'Commission not found'
            ], 404);
        }

        $commission->update([
            'status' => 'paid'
        ]);

        return response()->json(['success' => true]);
    }
}