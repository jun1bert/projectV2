@extends('layouts.dashboard')

@section('header', 'Customer Services')
@section('subheader', 'Services availed by each customer')

@section('content')

<style>
    .theme-card {
        background: rgba(250, 249, 246, .88);
        border: 1px solid rgba(164, 141, 120, .18);
        box-shadow: 0 18px 50px rgba(77, 64, 55, .08);
    }

    .theme-field {
        width: 100%;
        border-radius: 14px;
        border: 1px solid rgba(164, 141, 120, .25);
        background: rgba(250, 249, 246, .92);
        color: var(--ink);
        font-size: .875rem;
        outline: none;
    }

    .theme-field:focus {
        border-color: var(--desert-rock);
        box-shadow: 0 0 0 4px rgba(164, 141, 120, .16);
        background: #fff;
    }

    .theme-button { background: var(--desert-rock); color: #fff; }
    .theme-button:hover { background: #927865; }

    .muted-button {
        border: 1px solid rgba(164, 141, 120, .25);
        color: var(--ink);
        background: rgba(250, 249, 246, .8);
    }

    .muted-button:hover { background: rgba(230, 218, 200, .48); }
</style>

<section class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="theme-card rounded-2xl p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Customers</p>
            <p class="mt-2 text-3xl font-semibold text-[var(--ink)]">{{ $totalCustomers }}</p>
        </div>

        <div class="theme-card rounded-2xl p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Services Availed</p>
            <p class="mt-2 text-3xl font-semibold text-[var(--ink)]">{{ $totalServices }}</p>
        </div>

        <div class="theme-card rounded-2xl p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Revenue</p>
            <p class="mt-2 text-3xl font-semibold text-[var(--ink)]">PHP {{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.customer-services') }}" class="theme-card rounded-2xl p-4 md:p-5">
        <div class="grid gap-3 lg:grid-cols-[1fr_180px_160px_160px_130px_auto] lg:items-end">
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Customer</label>
                <input type="search" name="search" value="{{ $search }}" class="theme-field px-3 py-2.5" placeholder="Name, contact, or email">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Range</label>
                <select name="range" class="theme-field px-3 py-2.5" onchange="toggleCustomDates(this.value)">
                    <option value="today" @selected($range === 'today')>Today</option>
                    <option value="yesterday" @selected($range === 'yesterday')>Yesterday</option>
                    <option value="week" @selected($range === 'week')>This week</option>
                    <option value="month" @selected($range === 'month')>This month</option>
                    <option value="last_month" @selected($range === 'last_month')>Last month</option>
                    <option value="custom" @selected($range === 'custom')>Custom</option>
                </select>
            </div>

            <div class="custom-date-field {{ $range === 'custom' ? '' : 'hidden' }}">
                <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">From</label>
                <input type="date" name="from" value="{{ request('from', $from->toDateString()) }}" class="theme-field px-3 py-2.5">
            </div>

            <div class="custom-date-field {{ $range === 'custom' ? '' : 'hidden' }}">
                <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">To</label>
                <input type="date" name="to" value="{{ request('to', $to->toDateString()) }}" class="theme-field px-3 py-2.5">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Status</label>
                <select name="status" class="theme-field px-3 py-2.5">
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="all" @selected($status === 'all')>All</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Apply</button>
                <a href="{{ route('reports.customer-services') }}" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Reset</a>
            </div>
        </div>
    </form>

    <div class="space-y-4">
        @forelse($customers as $customer)
        <article class="theme-card overflow-hidden rounded-2xl">
            <div class="flex flex-col gap-3 border-b border-[rgba(164,141,120,.16)] p-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-[var(--ink)]">{{ $customer['name'] }}</h3>
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ $customer['contact'] }}</p>
                    @if($customer['email'])
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ $customer['email'] }}</p>
                    @endif
                    <button type="button"
                            class="muted-button mt-3 rounded-xl px-3 py-2 text-xs font-semibold transition"
                            onclick="openTransactionsModal({{ $loop->index }})">
                        Show Transactions
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs sm:min-w-[360px]">
                    <div class="rounded-xl bg-[rgba(230,218,200,.36)] px-3 py-2">
                        <span class="block text-[var(--muted)]">Visits</span>
                        <strong class="mt-1 block text-sm text-[var(--ink)]">{{ $customer['visits'] }}</strong>
                    </div>
                    <div class="rounded-xl bg-[rgba(230,218,200,.36)] px-3 py-2">
                        <span class="block text-[var(--muted)]">Spent</span>
                        <strong class="mt-1 block text-sm text-[var(--ink)]">PHP {{ number_format($customer['total_spent'], 2) }}</strong>
                    </div>
                    <div class="rounded-xl bg-[rgba(230,218,200,.36)] px-3 py-2">
                        <span class="block text-[var(--muted)]">Last Visit</span>
                        <strong class="mt-1 block text-sm text-[var(--ink)]">{{ $customer['last_visit'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            <div class="hidden md:block">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[rgba(230,218,200,.28)] text-xs uppercase tracking-wide text-[var(--muted)]">
                            <th class="px-5 py-3 text-left font-semibold">Service</th>
                            <th class="px-5 py-3 text-left font-semibold">Schedule</th>
                            <th class="px-5 py-3 text-left font-semibold">Staff</th>
                            <th class="px-5 py-3 text-left font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[rgba(164,141,120,.12)]">
                        @foreach($customer['services'] as $appointment)
                        <tr>
                            <td class="px-5 py-3 font-medium text-[var(--ink)]">{{ $appointment->service->name ?? 'No service' }}</td>
                            <td class="px-5 py-3 text-[var(--muted)]">{{ $appointment->date }} at {{ $appointment->time }}</td>
                            <td class="px-5 py-3 text-[var(--muted)]">{{ $appointment->assignedStaff->name ?? 'Unassigned' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-[rgba(230,218,200,.5)] px-2.5 py-1 text-xs font-semibold capitalize text-[var(--ink)]">
                                    {{ $appointment->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-[var(--desert-rock)]">PHP {{ number_format($appointment->service->price ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-[rgba(164,141,120,.12)] md:hidden">
                @foreach($customer['services'] as $appointment)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-[var(--ink)]">{{ $appointment->service->name ?? 'No service' }}</p>
                            <p class="mt-1 text-xs text-[var(--muted)]">{{ $appointment->date }} at {{ $appointment->time }}</p>
                        </div>
                        <span class="text-right text-sm font-semibold text-[var(--desert-rock)]">PHP {{ number_format($appointment->service->price ?? 0, 2) }}</span>
                    </div>
                    <p class="mt-2 text-xs text-[var(--muted)]">Staff: {{ $appointment->assignedStaff->name ?? 'Unassigned' }}</p>
                    <span class="mt-3 inline-flex rounded-full bg-[rgba(230,218,200,.5)] px-2.5 py-1 text-xs font-semibold capitalize text-[var(--ink)]">
                        {{ $appointment->status }}
                    </span>
                </div>
                @endforeach
            </div>
        </article>
        @empty
        <div class="theme-card rounded-2xl px-5 py-16 text-center">
            <h3 class="text-lg font-semibold text-[var(--ink)]">No customer services found</h3>
            <p class="mt-1 text-sm text-[var(--muted)]">Try a different date range, status, or customer search.</p>
        </div>
        @endforelse
    </div>
</section>

<div id="transactionsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeTransactionsModal()" aria-label="Close transactions modal"></button>

        <div class="theme-card relative w-full max-w-5xl overflow-hidden rounded-2xl">
            <div class="flex flex-col gap-3 border-b border-[rgba(164,141,120,.16)] px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 id="transactionsCustomerName" class="text-xl font-semibold text-[var(--ink)]">Customer Transactions</h2>
                    <p id="transactionsCustomerMeta" class="mt-1 text-sm text-[var(--muted)]"></p>
                </div>

                <button type="button" class="muted-button rounded-xl px-4 py-2 text-sm font-semibold transition" onclick="closeTransactionsModal()">
                    Close
                </button>
            </div>

            <div class="grid gap-3 border-b border-[rgba(164,141,120,.16)] p-5 sm:grid-cols-3">
                <div class="rounded-xl bg-[rgba(230,218,200,.36)] px-4 py-3">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Transactions</span>
                    <strong id="transactionsTotalCount" class="mt-1 block text-lg text-[var(--ink)]">0</strong>
                </div>
                <div class="rounded-xl bg-[rgba(230,218,200,.36)] px-4 py-3">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Total Spent</span>
                    <strong id="transactionsTotalSpent" class="mt-1 block text-lg text-[var(--ink)]">PHP 0.00</strong>
                </div>
                <div class="rounded-xl bg-[rgba(230,218,200,.36)] px-4 py-3">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Last Visit</span>
                    <strong id="transactionsLastVisit" class="mt-1 block text-lg text-[var(--ink)]">N/A</strong>
                </div>
            </div>

            <div class="max-h-[65vh] overflow-y-auto">
                <div class="hidden md:block">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-[var(--porcelain-mist)]">
                            <tr class="border-b border-[rgba(164,141,120,.16)] text-xs uppercase tracking-wide text-[var(--muted)]">
                                <th class="px-5 py-3 text-left font-semibold">Date</th>
                                <th class="px-5 py-3 text-left font-semibold">Service</th>
                                <th class="px-5 py-3 text-left font-semibold">Staff</th>
                                <th class="px-5 py-3 text-left font-semibold">Status</th>
                                <th class="px-5 py-3 text-left font-semibold">Payment</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTableBody" class="divide-y divide-[rgba(164,141,120,.12)]"></tbody>
                    </table>
                </div>

                <div id="transactionsMobileList" class="divide-y divide-[rgba(164,141,120,.12)] md:hidden"></div>
            </div>
        </div>
    </div>
</div>

<script>
const customerTransactions = @json($customerTransactions);

function formatMoney(value) {
    return `PHP ${(Number(value) || 0).toFixed(2)}`;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (match) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[match]));
}

function toggleCustomDates(range) {
    document.querySelectorAll('.custom-date-field').forEach((field) => {
        field.classList.toggle('hidden', range !== 'custom');
    });
}

function openTransactionsModal(index) {
    const customer = customerTransactions[index];
    if (!customer) return;

    document.getElementById('transactionsCustomerName').textContent = customer.name;
    const customerEmail = customer.email ? ` - ${customer.email}` : '';
    document.getElementById('transactionsCustomerMeta').textContent = `${customer.contact}${customerEmail} - ${customer.transactions.length} transaction${customer.transactions.length === 1 ? '' : 's'}`;
    document.getElementById('transactionsTotalCount').textContent = customer.transactions.length;
    document.getElementById('transactionsTotalSpent').textContent = formatMoney(customer.total_spent);
    document.getElementById('transactionsLastVisit').textContent = customer.last_visit || 'N/A';

    const tableBody = document.getElementById('transactionsTableBody');
    const mobileList = document.getElementById('transactionsMobileList');

    tableBody.innerHTML = customer.transactions.map((transaction) => {
        const receiptLink = transaction.receipt_url
            ? `<a href="${escapeHtml(transaction.receipt_url)}" target="_blank" class="font-semibold text-[var(--desert-rock)] hover:underline">Receipt</a>`
            : `<span class="text-[var(--muted)]">${escapeHtml(transaction.payment_status)}</span>`;

        return `
            <tr>
                <td class="px-5 py-3 text-[var(--muted)]">${escapeHtml(transaction.date)} at ${escapeHtml(transaction.time)}</td>
                <td class="px-5 py-3 font-medium text-[var(--ink)]">${escapeHtml(transaction.service)}</td>
                <td class="px-5 py-3 text-[var(--muted)]">${escapeHtml(transaction.staff)}</td>
                <td class="px-5 py-3">
                    <span class="rounded-full bg-[rgba(230,218,200,.5)] px-2.5 py-1 text-xs font-semibold capitalize text-[var(--ink)]">${escapeHtml(transaction.status)}</span>
                </td>
                <td class="px-5 py-3 capitalize">${receiptLink}</td>
                <td class="px-5 py-3 text-right font-semibold text-[var(--desert-rock)]">${formatMoney(transaction.amount)}</td>
            </tr>
        `;
    }).join('');

    mobileList.innerHTML = customer.transactions.map((transaction) => {
        const receiptLink = transaction.receipt_url
            ? `<a href="${escapeHtml(transaction.receipt_url)}" target="_blank" class="font-semibold text-[var(--desert-rock)] hover:underline">View receipt</a>`
            : `<span class="capitalize text-[var(--muted)]">${escapeHtml(transaction.payment_status)}</span>`;

        return `
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-[var(--ink)]">${escapeHtml(transaction.service)}</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">${escapeHtml(transaction.date)} at ${escapeHtml(transaction.time)}</p>
                    </div>
                    <span class="text-right text-sm font-semibold text-[var(--desert-rock)]">${formatMoney(transaction.amount)}</span>
                </div>
                <div class="mt-3 grid gap-1 text-xs text-[var(--muted)]">
                    <span>Staff: ${escapeHtml(transaction.staff)}</span>
                    <span>Booking: ${escapeHtml(transaction.booking_type)}</span>
                    <span>Payment: ${receiptLink}</span>
                </div>
                <span class="mt-3 inline-flex rounded-full bg-[rgba(230,218,200,.5)] px-2.5 py-1 text-xs font-semibold capitalize text-[var(--ink)]">
                    ${escapeHtml(transaction.status)}
                </span>
            </div>
        `;
    }).join('');

    document.getElementById('transactionsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTransactionsModal() {
    document.getElementById('transactionsModal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>

@endsection
