@extends('layouts.dashboard')

@section('header', 'Reports')
@section('subheader', 'Appointment, service, and revenue overview')
<title><?= config('app.name') ?> | Reports</title>
@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('reports.index') }}" class="theme-panel rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-2 sm:flex-row">
                <select name="range" onchange="this.form.submit()" class="theme-field rounded-xl px-4 py-3 text-sm">
                    <option value="today" @selected(($range ?? '') === 'today')>Today</option>
                    <option value="yesterday" @selected(($range ?? '') === 'yesterday')>Yesterday</option>
                    <option value="week" @selected(($range ?? '') === 'week')>This Week</option>
                    <option value="month" @selected(($range ?? 'month') === 'month')>This Month</option>
                    <option value="last_month" @selected(($range ?? '') === 'last_month')>Last Month</option>
                    <option value="custom" @selected(($range ?? '') === 'custom')>Custom Range</option>
                </select>

                @if(($range ?? '') === 'custom')
                    <input type="date" name="from" value="{{ request('from') }}" class="theme-field rounded-xl px-4 py-3 text-sm">
                    <input type="date" name="to" value="{{ request('to') }}" class="theme-field rounded-xl px-4 py-3 text-sm">
                    <button type="submit" class="rounded-xl bg-[var(--desert-rock)] px-5 py-3 text-sm font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                        Apply
                    </button>
                @endif
            </div>

            <a href="{{ route('reports.download', request()->query() + ['type' => 'summary']) }}"
               data-private-download
               class="rounded-xl bg-[var(--desert-rock)] px-5 py-3 text-center text-sm font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                Download Summary PDF
            </a>
        </div>
    </form>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <button type="button" data-report-target="summaryPanel" class="report-tile theme-card rounded-2xl p-4 text-left transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Total Appointments</p>
            <p class="mt-2 text-2xl font-bold text-[var(--ink)]">{{ $totalAppointments }}</p>
        </button>
        <button type="button" data-report-target="salesPanel" class="report-tile theme-card rounded-2xl p-4 text-left transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Completed</p>
            <p class="mt-2 text-2xl font-bold text-green-700">{{ $completed }}</p>
            <p class="mt-1 text-xs font-semibold text-[var(--desert-rock)]">View sales</p>
        </button>
        <button type="button" data-report-target="clientsPanel" class="report-tile theme-card rounded-2xl p-4 text-left transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Pending</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">{{ $pending }}</p>
            <p class="mt-1 text-xs font-semibold text-[var(--desert-rock)]">View clients</p>
        </button>
        <button type="button" data-report-target="salesPanel" class="report-tile theme-card rounded-2xl p-4 text-left transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Revenue</p>
            <p class="mt-2 text-2xl font-bold text-[var(--ink)]">&#8369;{{ number_format($totalRevenue, 2) }}</p>
            <p class="mt-1 text-xs font-semibold text-[var(--desert-rock)]">View sales</p>
        </button>
    </div>

    <div id="summaryPanel" class="report-panel grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="theme-card rounded-2xl">
            <div class="border-b border-[var(--soft-sandstone)]/35 px-5 py-4">
                <h3 class="text-sm font-bold text-[var(--ink)]">Appointment Status</h3>
            </div>

            <div class="space-y-4 p-5">
                @foreach([
                    ['Pending', $pending, 'bg-amber-400'],
                    ['Confirmed', $confirmed, 'bg-[var(--desert-rock)]'],
                    ['Completed', $completed, 'bg-green-500'],
                    ['Cancelled', $cancelled, 'bg-red-400'],
                ] as [$label, $count, $bar])
                    @php $pct = $totalAppointments > 0 ? round($count / $totalAppointments * 100) : 0; @endphp
                    <div>
                        <div class="mb-1.5 flex items-center justify-between text-xs">
                            <span class="font-semibold text-[var(--muted)]">{{ $label }}</span>
                            <span class="font-bold text-[var(--ink)]">{{ $count }} <span class="font-semibold text-[var(--muted)]">({{ $pct }}%)</span></span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-[var(--creamed-oat)]">
                            <div class="h-full rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="theme-card overflow-hidden rounded-2xl">
            <div class="border-b border-[var(--soft-sandstone)]/35 px-5 py-4">
                <h3 class="text-sm font-bold text-[var(--ink)]">Top Services</h3>
            </div>

            <table class="theme-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th class="text-right">Bookings</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--soft-sandstone)]/30">
                    @forelse($topServices as $item)
                        <tr>
                            <td class="px-5 py-3.5 text-[var(--ink)]">{{ $item->service->name ?? 'Unavailable' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="rounded-full bg-[var(--creamed-oat)] px-3 py-1 text-xs font-bold text-[var(--desert-rock)]">
                                    {{ $item->total }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-5 py-12 text-center text-sm text-[var(--muted)]">No service data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <section id="salesPanel" class="report-panel theme-card hidden overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-[var(--soft-sandstone)]/35 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-bold text-[var(--ink)]">Sales</h3>
                <p class="mt-1 text-xs text-[var(--muted)]">Completed appointments within the selected range.</p>
            </div>
            <a href="{{ route('reports.download', request()->query() + ['type' => 'sales']) }}"
               data-private-download
               class="rounded-xl bg-[var(--desert-rock)] px-4 py-2.5 text-center text-xs font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                Download Sales PDF
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="theme-table min-w-[720px]">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>Payment</th>
                        <th style="text-align: center">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--soft-sandstone)]/30">
                    @forelse($salesRows as $appointment)
                    <tr>
                        <td class="px-5 py-3.5 text-[var(--muted)]">{{ $appointment->date }} {{ $appointment->formatted_time }}</td>
                        <td class="px-5 py-3.5 text-[var(--ink)]">{{ $appointment->full_name }}</td>
                        <td class="px-5 py-3.5 text-[var(--muted)]">
                            <span class="block">{{ $appointment->contact_number }}</span>
                            @if($appointment->email)
                            <span class="block text-xs">{{ $appointment->email }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-[var(--muted)]">{{ $appointment->service->name ?? 'Unavailable' }}</td>
                        <td class="px-5 py-3.5 text-[var(--muted)]">{{ ucfirst($appointment->invoice->payment_method ?? 'unpaid') }}</td>
                        <td class="px-5 py-3.5 font-bold text-[var(--desert-rock)]" style="text-align: center">&#8369;{{ number_format($appointment->invoice?->amount_paid ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-[var(--muted)]">No sales data for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section id="clientsPanel" class="report-panel theme-card hidden overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-[var(--soft-sandstone)]/35 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-bold text-[var(--ink)]">Client List</h3>
                <p class="mt-1 text-xs text-[var(--muted)]">Private client contact list from appointments in this range.</p>
            </div>
            <a href="{{ route('reports.download', request()->query() + ['type' => 'clients']) }}"
               data-private-download
               class="rounded-xl bg-[var(--desert-rock)] px-4 py-2.5 text-center text-xs font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                Download Clients PDF
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="theme-table min-w-[680px]">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th class="text-right">Visits</th>
                        <th>Last Visit</th>
                        <th class="text-right">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--soft-sandstone)]/30">
                    @forelse($clientRows as $client)
                    <tr>
                        <td class="px-5 py-3.5 text-[var(--ink)]">{{ $client['name'] }}</td>
                        <td class="px-5 py-3.5 text-[var(--muted)]">{{ $client['contact'] }}</td>
                        <td class="px-5 py-3.5 text-[var(--muted)]">{{ $client['email'] ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-right text-[var(--ink)]">{{ $client['visits'] }}</td>
                        <td class="px-5 py-3.5 text-[var(--muted)]">{{ $client['last_visit'] ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-[var(--desert-rock)]">&#8369;{{ number_format($client['total_spent'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-[var(--muted)]">No client data for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="privacyModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closePrivacyModal()" aria-label="Close privacy warning"></button>

        <div class="theme-card relative w-full max-w-md rounded-2xl p-6">
            <h3 class="text-lg font-bold text-[var(--ink)]">Private Data Warning</h3>
            <p class="mt-3 text-sm leading-relaxed text-[var(--muted)]">
                This report may contain private client information, contact numbers, appointment history, and sales records.
                Download only when authorized, store it securely, and do not share it outside approved business use.
            </p>

            <div class="mt-6 grid gap-2 sm:grid-cols-2">
                <button type="button" onclick="closePrivacyModal()" class="rounded-xl border border-[var(--soft-sandstone)]/60 px-4 py-2.5 text-sm font-bold text-[var(--ink)] hover:bg-[var(--creamed-oat)]">
                    Cancel
                </button>
                <a id="privacyConfirmLink" href="#" class="rounded-xl bg-[var(--desert-rock)] px-4 py-2.5 text-center text-sm font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                    I Understand, Download
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .theme-field {
        border: 1px solid rgba(164, 141, 120, .28);
        background: rgba(250, 249, 246, .86);
        color: var(--ink);
        outline: none;
    }

    .theme-field:focus {
        border-color: var(--desert-rock);
        box-shadow: 0 0 0 3px rgba(164, 141, 120, .18);
    }

    .report-tile.is-active {
        border-color: var(--desert-rock);
        box-shadow: 0 16px 38px rgba(164, 141, 120, .18);
    }
</style>

<script>
const panels = document.querySelectorAll('.report-panel');
const tiles = document.querySelectorAll('.report-tile');
let pendingPrivateDownload = null;

function showReportPanel(id) {
    panels.forEach((panel) => panel.classList.toggle('hidden', panel.id !== id));
    tiles.forEach((tile) => tile.classList.toggle('is-active', tile.dataset.reportTarget === id));
}

tiles.forEach((tile) => {
    tile.addEventListener('click', () => showReportPanel(tile.dataset.reportTarget));
});

document.querySelectorAll('[data-private-download]').forEach((link) => {
    link.addEventListener('click', (event) => {
        event.preventDefault();
        pendingPrivateDownload = link.href;
        document.getElementById('privacyConfirmLink').href = pendingPrivateDownload;
        document.getElementById('privacyModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    });
});

function closePrivacyModal() {
    document.getElementById('privacyModal').classList.add('hidden');
    document.body.style.overflow = '';
    pendingPrivateDownload = null;
}

document.getElementById('privacyConfirmLink')?.addEventListener('click', () => {
    closePrivacyModal();
});
</script>
@endsection
