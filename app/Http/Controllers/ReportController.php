<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            return view('reports.index', $this->reportData($request));
        } catch (\Throwable $e) {
            Log::error('Report page failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Unable to load report. Please try again.');
        }
    }

    public function downloadReport(Request $request)
    {
        $type = in_array($request->query('type'), ['summary', 'sales', 'clients'], true)
            ? $request->query('type')
            : 'summary';

        $pdf = Pdf::loadView('reports.print', $this->reportData($request) + [
            'reportType' => $type,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($type.'-report-'.now()->format('Ymd-His').'.pdf');
    }

    public function customerServices(Request $request)
    {
        [$from, $to, $range] = $this->resolveDateRange($request);
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'completed');

        $appointmentsQuery = Appointment::with(['client', 'service', 'assignedStaffMembers', 'invoice'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('full_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->orderByDesc('date')
            ->orderByDesc('time');

        $appointments = $appointmentsQuery->get();

        $customers = $appointments
            ->groupBy(fn ($appointment) => ($appointment->client_id ?? 'appointment:'.$appointment->id)
                .'|'.mb_strtolower(trim($appointment->full_name)))
            ->map(function ($customerAppointments) {
                $first = $customerAppointments->first();
                $totalSpent = $customerAppointments->pluck('invoice')->filter()->unique('id')
                    ->sum(fn ($invoice) => (float) $invoice->amount_paid);

                return [
                    'name' => $first->full_name,
                    'contact' => $first->contact_number,
                    'email' => $customerAppointments->pluck('email')->filter()->first(),
                    'visits' => $customerAppointments->count(),
                    'total_spent' => $totalSpent,
                    'last_visit' => optional($customerAppointments->sortByDesc('date')->first())->date,
                    'services' => $customerAppointments,
                ];
            })
            ->sortBy('name')
            ->values();

        $customerTransactions = $customers->map(function ($customer) {
            return [
                'name' => $customer['name'],
                'contact' => $customer['contact'],
                'email' => $customer['email'],
                'visits' => $customer['visits'],
                'total_spent' => $customer['total_spent'],
                'last_visit' => $customer['last_visit'],
                'transactions' => $customer['services']->map(function ($appointment) {
                    return [
                        'date' => $appointment->date,
                        'time' => $appointment->formatted_time,
                        'service' => $appointment->service->name ?? 'No service',
                        'staff' => $appointment->assigned_staff_names,
                        'status' => $appointment->status,
                        'payment_status' => str_replace('_', ' ', $appointment->payment_status ?? 'unpaid'),
                        'booking_type' => $appointment->booking_type ?? 'online',
                        'amount' => (float) ($appointment->invoice?->amount_paid ?? 0),
                        'receipt_url' => $appointment->invoice
                            ? route('invoices.receipt', $appointment->invoice->id)
                            : null,
                    ];
                })->values(),
            ];
        })->values();

        return view('reports.customer-services', [
            'customers' => $customers,
            'customerTransactions' => $customerTransactions,
            'totalCustomers' => $customers->count(),
            'totalServices' => $appointments->count(),
            'totalRevenue' => $appointments->pluck('invoice')->filter()->unique('id')
                ->sum(fn ($invoice) => (float) $invoice->amount_paid),
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'status' => $status,
        ]);
    }

    private function reportData(Request $request): array
    {
        [$from, $to, $range] = $this->resolveDateRange($request);

        $dateFrom = $from->toDateString();
        $dateTo = $to->toDateString();

        $appointmentsQuery = Appointment::whereBetween('date', [$dateFrom, $dateTo]);

        $totalAppointments = (clone $appointmentsQuery)->count();
        $completed = (clone $appointmentsQuery)->where('status', 'completed')->count();
        $cancelled = (clone $appointmentsQuery)->where('status', 'cancelled')->count();
        $pending = (clone $appointmentsQuery)->where('status', 'pending')->count();
        $confirmed = (clone $appointmentsQuery)->where('status', 'confirmed')->count();

        $totalRevenue = Invoice::query()
            ->where('status', 'paid')
            ->whereHas('appointment', fn ($query) => $query->whereBetween('date', [$dateFrom, $dateTo]))
            ->sum('amount_paid');

        $topServices = Appointment::selectRaw('service_id, COUNT(*) as total')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->with('service')
            ->limit(10)
            ->get();

        $appointments = Appointment::with(['client', 'service', 'assignedStaffMembers', 'invoice'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $salesRows = $appointments
            ->where('status', 'completed')
            ->values();

        $clientRows = $appointments
            ->groupBy(fn ($appointment) => ($appointment->client_id ?? 'appointment:'.$appointment->id)
                .'|'.mb_strtolower(trim($appointment->full_name)))
            ->map(function ($clientAppointments) {
                $first = $clientAppointments->first();

                return [
                    'name' => $first->full_name,
                    'contact' => $first->contact_number,
                    'email' => $clientAppointments->pluck('email')->filter()->first(),
                    'visits' => $clientAppointments->count(),
                    'last_visit' => optional($clientAppointments->sortByDesc('date')->first())->date,
                    'total_spent' => $clientAppointments
                        ->pluck('invoice')->filter()->unique('id')
                        ->sum(fn ($invoice) => (float) $invoice->amount_paid),
                ];
            })
            ->sortBy('name')
            ->values();

        return compact(
            'range',
            'from',
            'to',
            'totalAppointments',
            'completed',
            'cancelled',
            'pending',
            'confirmed',
            'totalRevenue',
            'topServices',
            'appointments',
            'salesRows',
            'clientRows'
        );
    }

    private function resolveDateRange(Request $request): array
    {
        $range = $request->range ?? 'month';

        switch ($range) {
            case 'today':
                $from = Carbon::now()->startOfDay();
                $to = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $from = Carbon::now()->subDay()->startOfDay();
                $to = Carbon::now()->subDay()->endOfDay();
                break;
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
                break;
            case 'last_month':
                $from = Carbon::now()->subMonth()->startOfMonth();
                $to = Carbon::now()->subMonth()->endOfMonth();
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
                $from = Carbon::now()->startOfMonth();
                $to = Carbon::now()->endOfMonth();
                break;
        }

        return [$from, $to, $range];
    }
}
