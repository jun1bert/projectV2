<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            return view('reports.index', $this->reportData($request));
        } catch (\Throwable $e) {
            Log::error('Report page failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
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

        return $pdf->download($type . '-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function customerServices(Request $request)
    {
        [$from, $to, $range] = $this->resolveDateRange($request);
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'completed');

        $appointmentsQuery = Appointment::with(['service', 'assignedStaff', 'invoice'])
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
            ->groupBy(fn ($appointment) => Str::lower($appointment->full_name) . '|' . $appointment->contact_number)
            ->map(function ($customerAppointments) {
                $first = $customerAppointments->first();
                $totalSpent = $customerAppointments->sum(fn ($appointment) => (float) ($appointment->service->price ?? 0));

                return [
                    'name' => $first->full_name,
                    'contact' => $first->contact_number,
                    'email' => $first->email,
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
                        'time' => $appointment->time,
                        'service' => $appointment->service->name ?? 'No service',
                        'staff' => $appointment->assignedStaff->name ?? 'Unassigned',
                        'status' => $appointment->status,
                        'payment_status' => $appointment->payment_status ?? 'unpaid',
                        'booking_type' => $appointment->booking_type ?? 'online',
                        'amount' => (float) ($appointment->service->price ?? 0),
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
            'totalRevenue' => $appointments->sum(fn ($appointment) => (float) ($appointment->service->price ?? 0)),
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

        $totalRevenue = Appointment::whereBetween('appointments.date', [$dateFrom, $dateTo])
            ->where('appointments.status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');

        $topServices = Appointment::selectRaw('service_id, COUNT(*) as total')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->with('service')
            ->limit(10)
            ->get();

        $appointments = Appointment::with(['service', 'assignedStaff', 'invoice'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $clientEmails = User::query()
            ->whereIn('name', $appointments->pluck('full_name')->filter()->unique())
            ->pluck('email', 'name');

        $salesRows = $appointments
            ->where('status', 'completed')
            ->map(function ($appointment) use ($clientEmails) {
                $appointment->client_email = $appointment->email ?: ($clientEmails[$appointment->full_name] ?? null);

                return $appointment;
            })
            ->values();

        $clientRows = $appointments
            ->groupBy(fn ($appointment) => Str::lower($appointment->full_name) . '|' . $appointment->contact_number)
            ->map(function ($clientAppointments) use ($clientEmails) {
                $first = $clientAppointments->first();

                return [
                    'name' => $first->full_name,
                    'contact' => $first->contact_number,
                    'email' => $first->email ?: ($clientEmails[$first->full_name] ?? null),
                    'visits' => $clientAppointments->count(),
                    'last_visit' => optional($clientAppointments->sortByDesc('date')->first())->date,
                    'total_spent' => $clientAppointments
                        ->where('status', 'completed')
                        ->sum(fn ($appointment) => (float) ($appointment->service->price ?? 0)),
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
