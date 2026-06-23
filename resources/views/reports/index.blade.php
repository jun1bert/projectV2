@extends('layouts.dashboard')

@section('header', 'Reports')
@section('subheader', 'System analytics and performance overview')

@section('content')

{{-- ===================== DATE RANGE FILTER ===================== --}}
<form method="GET" action="{{ route('reports.index') }}"
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <a href="{{ route('reports.print', array_merge(request()->query(), ['autoprint' => 1])) }}"
   target="_blank"
   class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-xl transition">
    Print
</a>
    <div class="flex flex-col sm:flex-row gap-2 flex-1">
        <select name="range" onchange="this.form.submit()"
                class="w-full sm:w-48 px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700
                       focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
            <option value="today"      @selected(($range ?? '') === 'today')>Today</option>
            <option value="yesterday"  @selected(($range ?? '') === 'yesterday')>Yesterday</option>
            <option value="week"       @selected(($range ?? '') === 'week')>This Week</option>
            <option value="month"      @selected(($range ?? 'month') === 'month')>This Month</option>
            <option value="last_month" @selected(($range ?? '') === 'last_month')>Last Month</option>
            <option value="custom"     @selected(($range ?? '') === 'custom')>Custom range</option>
        </select>

        @if(($range ?? '') === 'custom')
        <div class="flex gap-2">
            <input type="date" name="from" value="{{ request('from') }}"
                   class="px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700
                          focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700
                          focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
            <button type="submit"
                    class="px-4 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition">
                Apply
            </button>
        </div>
        @endif
    </div>

</form>

{{-- ===================== KPI CARDS ===================== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

    {{-- Total Appointments --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-[#3a2a22]/5 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4.5 h-4.5 text-[#3a2a22]/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Total Appointments</p>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalAppointments }}</p>
        </div>
    </div>

    {{-- Completed --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4.5 h-4.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Completed</p>
            <p class="text-2xl font-bold text-green-600 leading-none">{{ $completed }}</p>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-[#c8a96a]/10 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4.5 h-4.5 text-[#c8a96a]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Revenue</p>
            <p class="text-2xl font-bold text-gray-900 leading-none">₱{{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

    {{-- Commissions --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4.5 h-4.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Commissions</p>
            <p class="text-2xl font-bold text-blue-600 leading-none">₱{{ number_format($totalCommission, 2) }}</p>
        </div>
    </div>

</div>

{{-- ===================== BOTTOM GRID ===================== --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- STATUS BREAKDOWN --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Appointment Status Breakdown</h3>
        </div>

        <div class="px-5 py-4 space-y-3">

            {{-- Pending --}}
            @php $pendingPct  = $totalAppointments > 0 ? round($pending  / $totalAppointments * 100) : 0; @endphp
            @php $completedPct= $totalAppointments > 0 ? round($completed/ $totalAppointments * 100) : 0; @endphp
            @php $cancelledPct= $totalAppointments > 0 ? round($cancelled/ $totalAppointments * 100) : 0; @endphp

            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                        <span class="text-gray-600">Pending</span>
                    </span>
                    <span class="font-semibold text-gray-800">{{ $pending }} <span class="text-gray-400 font-normal">({{ $pendingPct }}%)</span></span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-400 rounded-full transition-all" style="width: {{ $pendingPct }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-gray-600">Completed</span>
                    </span>
                    <span class="font-semibold text-gray-800">{{ $completed }} <span class="text-gray-400 font-normal">({{ $completedPct }}%)</span></span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all" style="width: {{ $completedPct }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-red-400"></span>
                        <span class="text-gray-600">Cancelled</span>
                    </span>
                    <span class="font-semibold text-gray-800">{{ $cancelled }} <span class="text-gray-400 font-normal">({{ $cancelledPct }}%)</span></span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-red-400 rounded-full transition-all" style="width: {{ $cancelledPct }}%"></div>
                </div>
            </div>

        </div>

    </div>

    {{-- TOP SERVICES --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Top Services</h3>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                    <th class="px-5 py-3 text-left">Service</th>
                    <th class="px-5 py-3 text-right">Bookings</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($topServices as $item)
                <tr class="hover:bg-[#faf7f2] transition-colors">
                    <td class="px-5 py-3.5 text-gray-800">{{ $item->service->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                     bg-[#c8a96a]/10 text-[#8a6a30] ring-1 ring-[#c8a96a]/30">
                            {{ $item->total }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-5 py-12 text-center">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <p class="text-gray-400 text-xs">No service data yet.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

    </div>

</div>

{{-- ===================== INVENTORY ===================== --}}
<div class="mt-4 bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">

    <div class="px-5 py-4 border-b border-gray-50">
        <h3 class="text-sm font-semibold text-gray-900">Inventory</h3>
        <p class="text-xs text-gray-400 mt-0.5">Consumption is within the selected date range</p>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Product</th>
                <th class="px-5 py-3 text-center">Unit</th>
                <th class="px-5 py-3 text-right">Used</th>
                <th class="px-5 py-3 text-right">Stock Left</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
        @forelse($inventoryStock as $product)
            @php
                $used  = $inventoryUsed[$product->name] ?? 0;
                $stock = $product->current_stock;
            @endphp
            <tr class="hover:bg-[#faf7f2] transition-colors">
                <td class="px-5 py-3.5 text-gray-800">{{ $product->name }}</td>
                <td class="px-5 py-3.5 text-center text-gray-500">{{ $product->unit ?? '—' }}</td>
                <td class="px-5 py-3.5 text-right">
                    @if($used > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                     bg-blue-50 text-blue-600 ring-1 ring-blue-200">
                            {{ $used }}
                        </span>
                    @else
                        <span class="text-gray-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $stock <= 0
                            ? 'bg-red-50 text-red-500 ring-1 ring-red-200'
                            : ($stock <= 5
                                ? 'bg-amber-50 text-amber-600 ring-1 ring-amber-200'
                                : 'bg-green-50 text-green-600 ring-1 ring-green-200') }}">
                        {{ $stock }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                    <p class="text-gray-400 text-xs">No products found.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

</div>
<script>
    const params = new URLSearchParams(window.location.search);

    if (params.get('autoprint') === '1') {
        window.onload = function () {
            window.print();
        };
    }
</script>
@endsection