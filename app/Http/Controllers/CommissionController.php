<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StaffCommission;

class CommissionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $totalEarnings = StaffCommission::where('status', 'paid')->sum('commission_amount');

        $monthlyEarnings = StaffCommission::where('status', 'paid')
            ->whereMonth('earned_at', now()->month)
            ->whereYear('earned_at', now()->year)
            ->sum('commission_amount');

        $pendingPayout = StaffCommission::where('status', 'pending')->sum('commission_amount');

        $paid = StaffCommission::where('status', 'paid')->sum('commission_amount');

        $staffEarnings = User::where('role', 'staff')
            ->withCount(['commissions as commissions_count'])
            ->withSum([
                'commissions as total_earnings' => function ($q) {
                    $q->where('status', 'paid');
                }
            ], 'commission_amount')
            ->get();

        return view('commissions.index', compact(
            'totalEarnings',
            'monthlyEarnings',
            'pendingPayout',
            'paid',
            'staffEarnings'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF DETAILS (MODAL)
    |--------------------------------------------------------------------------
    */
    public function details($staffId)
    {
        $staff = User::findOrFail($staffId);

        $commissions = StaffCommission::with(['appointment.service'])
            ->where('staff_id', $staffId)
            ->latest()
            ->get();

        return response()->json([
            'staff' => $staff->name,
            'total' => $commissions->sum('commission_amount'),
            'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'paid' => $commissions->where('status', 'paid')->sum('commission_amount'),

            'items' => $commissions->map(function ($c) {
                return [
                    'id' => $c->id,
                    'service' => $c->appointment->service->name ?? 'N/A',
                    'date' => $c->appointment->date ?? '',
                    'time' => $c->appointment->time ?? '',
                    'amount' => $c->service_amount,
                    'commission' => $c->commission_amount,
                    'status' => $c->status,
                ];
            }),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MARK AS PAID (SERVICE HANDLED OUTSIDE)
    |--------------------------------------------------------------------------
    */
 public function markPaid($id)
{
    \Log::info('MARK PAID ID RECEIVED', ['id' => $id]);

    $commission = StaffCommission::find($id);

    if (!$commission) {
        \Log::warning('COMMISSION NOT FOUND', ['id' => $id]);

        return response()->json([
            'success' => false,
            'message' => 'Commission not found',
            'debug_id' => $id
        ], 404);
    }

    $commission->update([
        'status' => 'paid',
        'earned_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Commission marked as paid'
    ]);
}
}