<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\StaffCommission;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            [$from, $to, $range] = $this->resolveDateRange($request);

            $appointmentsQuery = Appointment::whereBetween('created_at', [$from, $to]);

            $totalAppointments = (clone $appointmentsQuery)->count();
            $completed         = (clone $appointmentsQuery)->where('status', 'completed')->count();
            $rejected          = (clone $appointmentsQuery)->where('status', 'rejected')->count();
            $pending           = (clone $appointmentsQuery)->where('status', 'pending')->count();

            $totalRevenue = Appointment::whereBetween('appointments.created_at', [$from, $to])
                ->where('appointments.status', 'completed')
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->sum('services.price');

            $totalCommission = StaffCommission::whereHas('appointment', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })->sum('commission_amount');

            $topServices = Appointment::selectRaw('service_id, COUNT(*) as total')
                ->whereBetween('created_at', [$from, $to])
                ->groupBy('service_id')
                ->orderByDesc('total')
                ->with('service')
                ->limit(5)
                ->get();

            $inventoryStock = Product::orderBy('name')->get();

            $inventoryUsed = InvoiceItem::where('type', 'item')
                ->whereHas('invoice', function ($q) use ($from, $to) {
                    $q->whereHas('appointment', function ($q2) use ($from, $to) {
                        $q2->whereBetween('date', [$from, $to])
                           ->where('status', 'completed');
                    });
                })
                ->get()
                ->groupBy('name')
                ->map(fn($items) => $items->sum('qty'));

            return view('reports.index', compact(
                'range', 'from', 'to',
                'totalAppointments', 'completed', 'rejected', 'pending',
                'totalRevenue', 'totalCommission',
                'topServices',
                'inventoryStock', 'inventoryUsed'
            ));

        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRINT REPORT
    |--------------------------------------------------------------------------
    */
    public function print(Request $request)
    {
        [$from, $to, $range] = $this->resolveDateRange($request);

        // ── KPI ──────────────────────────────────────────────────────────────
        $appointmentsQuery = Appointment::whereBetween('created_at', [$from, $to]);

        $totalAppointments = (clone $appointmentsQuery)->count();
        $completed         = (clone $appointmentsQuery)->where('status', 'completed')->count();
        $pending           = (clone $appointmentsQuery)->where('status', 'pending')->count();
        $confirmed         = (clone $appointmentsQuery)->where('status', 'confirmed')->count();
        $rejected          = (clone $appointmentsQuery)->where('status', 'rejected')->count();

        $totalRevenue = Appointment::whereBetween('appointments.created_at', [$from, $to])
            ->where('appointments.status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        $totalCommission = StaffCommission::whereHas('appointment', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to]);
        })->sum('commission_amount');

        // ── TOP SERVICES ─────────────────────────────────────────────────────
        $topServices = Appointment::selectRaw('service_id, COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->with('service')
            ->limit(10)
            ->get();

        // ── APPOINTMENTS DETAIL ───────────────────────────────────────────────
        $appointments = Appointment::with(['service', 'assignedStaff', 'invoice.items'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        // ── PER-STAFF COMMISSION BREAKDOWN ────────────────────────────────────
        $staffCommissions = StaffCommission::with(['staff', 'service', 'appointment'])
            ->whereHas('appointment', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })
            ->get()
            ->groupBy('staff_id')
            ->map(function ($commissions) {
                $staff = $commissions->first()->staff;
                return [
                    'name'    => $staff->name ?? 'Unknown',
                    'total'   => $commissions->sum('commission_amount'),
                    'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
                    'paid'    => $commissions->where('status', 'paid')->sum('commission_amount'),
                    'count'   => $commissions->count(),
                    'items'   => $commissions,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // ── INVENTORY ─────────────────────────────────────────────────────────
        $inventoryStock = Product::orderBy('name')->get();

        $inventoryUsed = InvoiceItem::where('type', 'item')
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q->whereHas('appointment', function ($q2) use ($from, $to) {
                    $q2->whereBetween('date', [$from, $to])
                       ->where('status', 'completed');
                });
            })
            ->get()
            ->groupBy('name')
            ->map(function ($items) {
                return [
                    'name'       => $items->first()->name,
                    'total_qty'  => $items->sum('qty'),
                    'total_cost' => $items->sum('subtotal'),
                ];
            })
            ->sortByDesc('total_cost')
            ->values();

        $totalInventoryCost = $inventoryUsed->sum('total_cost');

        return view('reports.print', compact(
            'range', 'from', 'to',
            'totalAppointments', 'completed', 'rejected', 'pending', 'confirmed',
            'totalRevenue', 'totalCommission',
            'topServices',
            'appointments',
            'staffCommissions',
            'inventoryStock', 'inventoryUsed', 'totalInventoryCost'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DATE RANGE RESOLVER
    |--------------------------------------------------------------------------
    */
    private function resolveDateRange(Request $request): array
    {
        $range = $request->range ?? 'month';

        switch ($range) {
            case 'today':
                $from = Carbon::now()->startOfDay();
                $to   = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $from = Carbon::now()->subDay()->startOfDay();
                $to   = Carbon::now()->subDay()->endOfDay();
                break;
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $to   = Carbon::now()->endOfWeek();
                break;
            case 'last_month':
                $from = Carbon::now()->subMonth()->startOfMonth();
                $to   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $from = $request->from
                    ? Carbon::parse($request->from)->startOfDay()
                    : Carbon::now()->startOfMonth();
                $to = $request->to
                    ? Carbon::parse($request->to)->endOfDay()
                    : Carbon::now()->endOfMonth();
                break;
            default:
                $range = 'month';
                $from  = Carbon::now()->startOfMonth();
                $to    = Carbon::now()->endOfMonth();
                break;
        }

        return [$from, $to, $range];
    }

    

public function downloadReport(Request $request)
{
    [$from, $to, $range] = $this->resolveDateRange($request);

    $appointmentsQuery = Appointment::whereBetween('created_at', [$from, $to]);

    $totalAppointments = (clone $appointmentsQuery)->count();
    $completed         = (clone $appointmentsQuery)->where('status', 'completed')->count();
    $rejected          = (clone $appointmentsQuery)->where('status', 'rejected')->count();
    $pending           = (clone $appointmentsQuery)->where('status', 'pending')->count();
    $confirmed         = (clone $appointmentsQuery)->where('status', 'confirmed')->count();

    $totalRevenue = Appointment::whereBetween('appointments.created_at', [$from, $to])
        ->where('appointments.status', 'completed')
        ->join('services', 'appointments.service_id', '=', 'services.id')
        ->sum('services.price');

    $totalCommission = StaffCommission::whereHas('appointment', function ($q) use ($from, $to) {
        $q->whereBetween('created_at', [$from, $to]);
    })->sum('commission_amount');

    $topServices = Appointment::selectRaw('service_id, COUNT(*) as total')
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('service_id')
        ->orderByDesc('total')
        ->with('service')
        ->limit(10)
        ->get();

    $appointments = Appointment::with(['service', 'assignedStaff', 'invoice.items'])
        ->whereBetween('created_at', [$from, $to])
        ->orderBy('date')
        ->orderBy('time')
        ->get();

    $staffCommissions = StaffCommission::with(['staff', 'service', 'appointment'])
        ->whereHas('appointment', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to]);
        })
        ->get()
        ->groupBy('staff_id')
        ->map(function ($commissions) {
            $staff = $commissions->first()->staff;

            return [
                'name'    => $staff->name ?? 'Unknown',
                'total'   => $commissions->sum('commission_amount'),
                'pending' => $commissions->where('status', 'pending')->sum('commission_amount'),
                'paid'    => $commissions->where('status', 'paid')->sum('commission_amount'),
                'count'   => $commissions->count(),
                'items'   => $commissions,
            ];
        })
        ->sortByDesc('total')
        ->values();

    $inventoryStock = Product::orderBy('name')->get();

    $inventoryUsed = InvoiceItem::where('type', 'item')
        ->whereHas('invoice', function ($q) use ($from, $to) {
            $q->whereHas('appointment', function ($q2) use ($from, $to) {
                $q2->whereBetween('date', [$from, $to])
                   ->where('status', 'completed');
            });
        })
        ->get()
        ->groupBy('name')
        ->map(function ($items) {
            return [
                'name'       => $items->first()->name,
                'total_qty'  => $items->sum('qty'),
                'total_cost' => $items->sum('subtotal'),
            ];
        })
        ->values();

    $totalInventoryCost = $inventoryUsed->sum('total_cost');

    $pdf = Pdf::loadView('reports.print', compact(
        'range',
        'from',
        'to',
        'totalAppointments',
        'completed',
        'rejected',
        'pending',
        'confirmed',
        'totalRevenue',
        'totalCommission',
        'topServices',
        'appointments',
        'staffCommissions',
        'inventoryStock',
        'inventoryUsed',
        'totalInventoryCost'
    ))->setPaper('a4', 'portrait');

    return $pdf->download(
        'business-report-' . now()->format('Ymd-His') . '.pdf'
    );
}
}