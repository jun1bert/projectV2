
@extends('layouts.dashboard')

@section('header', 'Appointments')
@section('subheader', 'Manage and update appointment status')

@section('content')

@if(session('success'))
<div id="toast-success"
     class="fixed top-4 right-4 z-[100] flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl shadow-lg text-sm max-w-xs"
     role="alert">
    <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span>{{ session('success') }}</span>
    <button onclick="this.closest('#toast-success').remove()" class="ml-auto text-green-400 hover:text-green-600">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<script>
    setTimeout(() => {
        const t = document.getElementById('toast-success');
        if (t) t.remove();
    }, 4000);
</script>
@endif

@php
    $canManage = in_array(auth()->user()->role, ['admin','management','staff']);
    $canAssign = in_array(auth()->user()->role, ['admin','management']);
@endphp

{{-- ===================== TOOLBAR ===================== --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">

    <div class="flex flex-col sm:flex-row gap-2 flex-1">

        <div class="relative flex-1 min-w-0 sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35m0 0A7 7 0 1110 3a7 7 0 016.65 13.65z"/>
            </svg>
            <input type="text"
                   id="globalSearch"
                   placeholder="Search name, contact, service…"
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
        </div>

        <select id="statusFilter"
                class="w-full sm:w-40 px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="rejected">Rejected</option>
            <option value="completed">Completed</option>
        </select>

    </div>

    @if($canManage)
    <button type="button" onclick="openWalkInModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New walk-in
    </button>
    @endif

</div>

{{-- ===================== DESKTOP TABLE ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm" id="desktopTable">

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                <th class="px-5 py-3.5 text-left">Client</th>
                <th class="px-5 py-3.5 text-left">Service</th>
                <th class="px-5 py-3.5 text-left">Date & Time</th>
                <th class="px-5 py-3.5 text-left">Status</th>
                @if($canManage)
                <th class="px-5 py-3.5 text-left">Actions</th>
                @endif
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-50">
        @forelse($appointments as $a)

        <tr class="filter-row hover:bg-[#faf7f2] transition-colors"
            data-search="{{ strtolower($a->full_name.' '.$a->contact_number.' '.($a->service->name ?? '').' '.$a->status.' '.($a->booking_type ?? 'online')) }}"
            data-status="{{ $a->status }}">

            {{-- CLIENT --}}
            <td class="px-5 py-4">
                <p class="font-medium text-gray-900">{{ $a->full_name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $a->contact_number }}</p>
            </td>

            {{-- SERVICE --}}
            <td class="px-5 py-4" data-price="{{ $a->service->price ?? 0 }}">
                <span class="text-gray-800">{{ $a->service->name ?? '—' }}</span>
                @if($a->service->price ?? false)
                <span class="block text-xs text-[#c8a96a] mt-0.5">₱{{ number_format($a->service->price, 2) }}</span>
                @endif
            </td>

            {{-- DATE & TIME --}}
            <td class="px-5 py-4">
                <p class="text-gray-800">{{ $a->date }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $a->time }}</p>
            </td>

            {{-- STATUS --}}
            <td class="px-5 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    @if($a->status=='pending')    bg-amber-50  text-amber-700  ring-1 ring-amber-200
                    @elseif($a->status=='confirmed') bg-green-50  text-green-700  ring-1 ring-green-200
                    @elseif($a->status=='rejected')  bg-red-50    text-red-700    ring-1 ring-red-200
                    @elseif($a->status=='completed') bg-[#c8a96a]/10 text-[#8a6a30] ring-1 ring-[#c8a96a]/30
                    @endif">
                    {{ ucfirst($a->status) }}
                </span>
                @if(($a->payment_status ?? null) === 'paid')
                <span class="ml-2 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Paid
                </span>
                @endif
            </td>

            {{-- ACTIONS --}}
            @if($canManage)
            <td class="px-5 py-4">
                <div class="flex flex-wrap gap-2 items-center">

                    <select class="status-select px-3 py-2 rounded-lg text-xs border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition"
                            data-id="{{ $a->id }}">
                        <option value="pending"   @selected($a->status=='pending')>Pending</option>
                        <option value="confirmed" @selected($a->status=='confirmed')>Confirmed</option>
                        <option value="rejected"  @selected($a->status=='rejected')>Rejected</option>
                        <option value="completed" @selected($a->status=='completed')>Completed</option>
                    </select>

                    @if($canAssign)
                    <select class="assign-select px-3 py-2 rounded-lg text-xs border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition"
                            data-id="{{ $a->id }}">
                        <option value="">Unassigned</option>
                        @foreach($staff ?? [] as $member)
                            <option value="{{ $member->id }}" @selected($a->assigned_to == $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    @endif

                    @if($a->status === 'completed' && ($a->payment_status ?? null) !== 'paid')
                    <button type="button"
                            class="pay-btn inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white transition"
                            data-id="{{ $a->id }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Payment
                    </button>
                    @elseif($a->status === 'completed' && ($a->payment_status ?? null) === 'paid')
                    <a href="{{ url('/invoices') }}/{{ $a->invoice->id ?? '' }}/receipt"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Receipt
                    </a>
                    @endif

                </div>
            </td>
            @endif

        </tr>

        @empty
        <tr>
            <td colspan="5" class="px-5 py-16 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-400 text-sm">No appointments yet.</p>
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>

</div>

{{-- ===================== MOBILE CARDS ===================== --}}
<div class="grid gap-3" id="mobileCards">

@forelse($appointments as $a)

<div class="filter-row bg-white border border-gray-100 rounded-2xl p-4 shadow-sm"
     data-search="{{ strtolower($a->full_name.' '.$a->contact_number.' '.($a->service->name ?? '').' '.$a->status.' '.($a->booking_type ?? 'online')) }}"
     data-status="{{ $a->status }}">

    <div class="flex justify-between items-start gap-2 mb-3">
        <div>
            <p class="font-semibold text-gray-900 text-sm">{{ $a->full_name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $a->contact_number }}</p>
        </div>

        <div class="flex flex-wrap gap-1.5 justify-end">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                @if($a->status=='pending')    bg-amber-50  text-amber-700  ring-1 ring-amber-200
                @elseif($a->status=='confirmed') bg-green-50  text-green-700  ring-1 ring-green-200
                @elseif($a->status=='rejected')  bg-red-50    text-red-700    ring-1 ring-red-200
                @elseif($a->status=='completed') bg-[#c8a96a]/10 text-[#8a6a30] ring-1 ring-[#c8a96a]/30
                @endif">
                {{ ucfirst($a->status) }}
            </span>
            @if(($a->payment_status ?? null) === 'paid')
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Paid
            </span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-4 text-xs text-gray-500 mb-3"
         data-price="{{ $a->service->price ?? 0 }}">
        <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-[#c8a96a]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            {{ $a->service->name ?? '—' }}
        </span>
        <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $a->date }}
        </span>
        <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $a->time }}
        </span>
    </div>

    @if($canManage)
    <div class="border-t border-gray-100 pt-3 flex flex-wrap gap-2">

        <select class="status-select flex-1 min-w-[120px] px-3 py-2 rounded-xl text-xs border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50"
                data-id="{{ $a->id }}">
            <option value="pending"   @selected($a->status=='pending')>Pending</option>
            <option value="confirmed" @selected($a->status=='confirmed')>Confirmed</option>
            <option value="rejected"  @selected($a->status=='rejected')>Rejected</option>
            <option value="completed" @selected($a->status=='completed')>Completed</option>
        </select>

        @if($canAssign)
        <select class="assign-select flex-1 min-w-[120px] px-3 py-2 rounded-xl text-xs border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50"
                data-id="{{ $a->id }}">
            <option value="">Unassigned</option>
            @foreach($staff ?? [] as $member)
                <option value="{{ $member->id }}" @selected($a->assigned_to == $member->id)>{{ $member->name }}</option>
            @endforeach
        </select>
        @endif

        @if($a->status === 'completed' && ($a->payment_status ?? null) !== 'paid')
        <button type="button"
                class="pay-btn inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white transition"
                data-id="{{ $a->id }}">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Payment
        </button>
        @elseif($a->status === 'completed' && ($a->payment_status ?? null) === 'paid')
        <a href="{{ url('/invoices') }}/{{ $a->invoice->id ?? '' }}/receipt"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Receipt
        </a>
        @endif

    </div>
    @endif

</div>

@empty
<div class="text-center py-16 text-gray-400 text-sm">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    No appointments yet.
</div>
@endforelse

</div>

<style>
#desktopTable { display: none; }
@media (min-width: 768px) {
    #desktopTable { display: block; }
    #mobileCards  { display: none; }
}
</style>

{{-- ===================== WALK-IN MODAL ===================== --}}
@if($canManage)
<div id="walkInModal"
     class="hidden fixed inset-0 z-50 overflow-y-auto"
     role="dialog" aria-modal="true" aria-labelledby="walkInTitle">

    <div class="flex min-h-full items-center justify-center p-4">
        <div id="walkInBackdrop" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 id="walkInTitle" class="text-base font-semibold text-gray-900">New walk-in appointment</h2>
                <button id="closeWalkInModal" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="walkInForm" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Full name</label>
                    <input type="text" name="full_name" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition"
                           placeholder="e.g. Juan Dela Cruz">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Contact number</label>
                    <input type="text" name="contact_number" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition"
                           placeholder="09XX XXX XXXX">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Service</label>
                    <select name="service_id" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition bg-white">
                        <option value="">Select a service…</option>
                        @foreach($services ?? [] as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Date</label>
                        <input type="date" name="date" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Time</label>
                        <input type="time" name="time" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                </div>

                @if($canAssign)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Assign staff <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="assigned_to"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition bg-white">
                        <option value="">Unassigned</option>
                        @foreach($staff ?? [] as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex gap-2 pt-1">
                    <button type="button" id="cancelWalkIn"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Create appointment
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endif

{{-- ===================== PAYMENT MODAL ===================== --}}
<div id="paymentModal"
     class="hidden fixed inset-0 z-50 overflow-y-auto"
     role="dialog" aria-modal="true" aria-labelledby="paymentTitle">

    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
        <div id="paymentBackdrop" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 id="paymentTitle" class="text-base font-semibold text-gray-900">Process payment</h2>
                <button id="closePaymentModal" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 max-h-[80vh] overflow-y-auto">
                <form id="paymentForm" class="space-y-4">
                    @csrf

                    <input type="hidden" name="appointment_id" id="payment_appointment_id">

                    <div class="bg-[#faf7f2] border border-[#c8a96a]/20 rounded-xl p-4 text-sm space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Service</span>
                            <span id="pm_service_price" class="font-medium text-gray-800">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Items</span>
                            <span id="pm_items_price" class="font-medium text-gray-800">₱0.00</span>
                        </div>
                        <div class="border-t border-[#c8a96a]/20 pt-2 flex justify-between">
                            <span class="font-semibold text-gray-800">Total</span>
                            <span id="pm_total" class="font-bold text-[#c8a96a] text-base">₱0.00</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-medium text-gray-600">Items used</label>
                            <button type="button" id="addItemBtn"
                                    class="inline-flex items-center gap-1 text-xs text-[#c8a96a] hover:text-[#b8955a] font-medium transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add item
                            </button>
                        </div>
                        <div id="itemsContainer" class="space-y-2"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Amount due</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">₱</span>
                            <input type="number" name="amount" id="payment_amount" readonly
                                   class="w-full pl-7 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-700 cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Payment method</label>
                        <select name="method" id="payment_method" required
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                            <option value="">Select method…</option>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="notes" rows="2"
                                  class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition resize-none"
                                  placeholder="Any additional notes…"></textarea>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" id="cancelPayment"
                                class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" id="paymentSubmitBtn"
                                class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Confirm payment
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== SCRIPTS ===================== --}}
<script>
const paymentModal   = document.getElementById('paymentModal');
const paymentForm    = document.getElementById('paymentForm');
const itemsContainer = document.getElementById('itemsContainer');
const walkInModal    = document.getElementById('walkInModal');

let currentServicePrice = 0;
let currentPayBtn = null;

function openWalkInModal() {
    walkInModal?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeWalkIn() {
    walkInModal?.classList.add('hidden');
    document.body.style.overflow = '';
}

document.getElementById('closeWalkInModal')?.addEventListener('click', closeWalkIn);
document.getElementById('cancelWalkIn')?.addEventListener('click', closeWalkIn);
document.getElementById('walkInBackdrop')?.addEventListener('click', closeWalkIn);

function calculatePaymentTotal(servicePrice = currentServicePrice) {
    let itemTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.dataset.price || 0);
        itemTotal  += qty * price;
    });
    const total = (parseFloat(servicePrice) || 0) + itemTotal;
    document.getElementById('pm_service_price').innerText = '₱' + (parseFloat(servicePrice) || 0).toFixed(2);
    document.getElementById('pm_items_price').innerText   = '₱' + itemTotal.toFixed(2);
    document.getElementById('pm_total').innerText         = '₱' + total.toFixed(2);
    document.getElementById('payment_amount').value       = total.toFixed(2);
}

function triggerRecalc() { calculatePaymentTotal(currentServicePrice); }

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.pay-btn');
    if (!btn) return;
    currentPayBtn = btn;
    const row = btn.closest('tr') || btn.closest('.filter-row');
    currentServicePrice = parseFloat(row?.querySelector('[data-price]')?.dataset.price || 0);
    document.getElementById('payment_appointment_id').value = btn.dataset.id;
    itemsContainer.innerHTML = '';
    itemsContainer.appendChild(createItemRow());
    calculatePaymentTotal(currentServicePrice);
    paymentModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
});

function closePayment() {
    paymentModal.classList.add('hidden');
    document.body.style.overflow = '';
    const submitBtn = document.getElementById('paymentSubmitBtn');
    submitBtn.disabled = false;
    submitBtn.innerHTML = `
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Confirm payment
    `;
}

function createItemRow() {
    const row = document.createElement('div');
    row.className = 'item-row flex gap-2 relative items-center';
    row.innerHTML = `
        <div class="relative flex-1">
            <input type="text"
                   class="item-search w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition"
                   placeholder="Search item…" autocomplete="off">
            <div class="search-results hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-48 overflow-y-auto"></div>
        </div>
        <input type="hidden" class="item-id">
        <input type="number"
               class="item-qty w-16 px-2 py-2 border border-gray-200 rounded-xl text-xs text-center focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition"
               placeholder="Qty" min="1">
        <span class="item-price-display text-xs text-gray-500 w-16 text-right shrink-0">₱0.00</span>
        <button type="button"
                class="remove-item w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    row.querySelector('.remove-item').addEventListener('click', () => { row.remove(); triggerRecalc(); });
    return row;
}

document.getElementById('addItemBtn').addEventListener('click', () => {
    itemsContainer.appendChild(createItemRow());
});

document.addEventListener('input', async function (e) {
    if (!e.target.classList.contains('item-search')) return;
    const input    = e.target;
    const row      = input.closest('.item-row');
    const query    = input.value.trim();
    const dropdown = row.querySelector('.search-results');
    if (query.length < 2) { dropdown.classList.add('hidden'); return; }
    let data = [];
    try {
        const res = await fetch(`/inventory/search?q=${encodeURIComponent(query)}`);
        data = await res.json();
    } catch (err) { console.error('Inventory search failed', err); return; }
    dropdown.innerHTML = '';
    if (!data.length) { dropdown.classList.add('hidden'); return; }
    data.forEach(item => {
        const div = document.createElement('div');
        div.className = 'px-3 py-2.5 hover:bg-[#faf7f2] cursor-pointer text-xs transition border-b border-gray-50 last:border-0';
        div.innerHTML = `
            <div class="font-medium text-gray-800">${item.name}</div>
            <div class="text-gray-400 mt-0.5">Stock: ${item.stock} &nbsp;·&nbsp; ₱${parseFloat(item.cost_price).toFixed(2)}</div>
        `;
        div.onclick = () => {
            input.value = item.name;
            row.querySelector('.item-id').value = item.id;
            row.dataset.price = item.cost_price;
            row.querySelector('.item-price-display').innerText = '₱' + parseFloat(item.cost_price).toFixed(2);
            dropdown.classList.add('hidden');
            triggerRecalc();
        };
        dropdown.appendChild(div);
    });
    dropdown.classList.remove('hidden');
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('item-search')) return;
    document.querySelectorAll('.search-results').forEach(d => {
        if (!d.contains(e.target)) d.classList.add('hidden');
    });
});

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('item-qty')) triggerRecalc();
});

paymentForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    const submitBtn = document.getElementById('paymentSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        Processing…
    `;
    const rows  = document.querySelectorAll('.item-row');
    const items = [];
    rows.forEach(row => {
        const id    = row.querySelector('.item-id')?.value;
        const qty   = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.dataset.price || 0);
        if (id && qty > 0) items.push({ product_id: id, qty, price });
    });
    const formData = new FormData(paymentForm);
    formData.append('items', JSON.stringify(items));
    const id = document.getElementById('payment_appointment_id').value;
    try {
        const res = await fetch(`/appointments/${id}/payment`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            window.open(`/invoices/${data.invoice_id}/receipt`, '_blank');
            if (currentPayBtn) {
                const row = currentPayBtn.closest('tr') || currentPayBtn.closest('.filter-row');
                const receiptLink = document.createElement('a');
                receiptLink.href = `/invoices/${data.invoice_id}/receipt`;
                receiptLink.target = '_blank';
                receiptLink.className = 'inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition';
                receiptLink.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Receipt
                `;
                currentPayBtn.replaceWith(receiptLink);
                const statusCell = row.querySelector('[class*="bg-[#c8a96a]"], [class*="bg-amber-50"], [class*="bg-green-50"], [class*="bg-red-50"]');
                if (statusCell) {
                    const paidBadge = document.createElement('span');
                    paidBadge.className = 'ml-2 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
                    paidBadge.innerHTML = `<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Paid`;
                    statusCell.parentElement.appendChild(paidBadge);
                }
            }
            closePayment();
        } else {
            alert(data.message || 'Payment failed.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Confirm payment`;
        }
    } catch (err) {
        console.error('Payment submission failed', err);
        alert('Something went wrong while processing the payment.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Confirm payment`;
    }
});

const globalSearchInput  = document.getElementById('globalSearch');
const statusFilterSelect = document.getElementById('statusFilter');

function applyFilters() {
    const search = (globalSearchInput.value || '').trim().toLowerCase();
    const status = statusFilterSelect.value;
    document.querySelectorAll('.filter-row').forEach(row => {
        const matchesSearch = !search || row.dataset.search.includes(search);
        const matchesStatus = !status || row.dataset.status === status;
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

globalSearchInput?.addEventListener('input', applyFilters);
statusFilterSelect?.addEventListener('change', applyFilters);

document.addEventListener('change', async function (e) {
    if (!e.target.classList.contains('status-select')) return;
    const select     = e.target;
    const id         = select.dataset.id;
    const status     = select.value;
    const assignSel  = document.querySelector(`.assign-select[data-id="${id}"]`);
    const assignedTo = assignSel ? assignSel.value : null;
    try {
        const res = await fetch(`/appointments/${id}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ status, assigned_to: assignedTo || null })
        });
        const data = await res.json();
        if (!data.success) { alert(data.message || 'Failed to update status.'); } else { location.reload(); }
    } catch (err) { console.error('Status update failed', err); alert('Something went wrong while updating the status.'); }
});

document.addEventListener('change', async function (e) {
    if (!e.target.classList.contains('assign-select')) return;
    const assignSel = e.target;
    const id        = assignSel.dataset.id;
    const statusSel = document.querySelector(`.status-select[data-id="${id}"]`);
    const status    = statusSel ? statusSel.value : null;
    if (!status) return;
    try {
        const res = await fetch(`/appointments/${id}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ status, assigned_to: assignSel.value || null })
        });
        const data = await res.json();
        if (!data.success) { alert(data.message || 'Failed to update assignment.'); } else { location.reload(); }
    } catch (err) { console.error('Assignment update failed', err); alert('Something went wrong while updating the assignment.'); }
});

document.querySelectorAll('.status-select').forEach(sel => {
    sel.dataset.previousValue = sel.value;
    sel.addEventListener('focus', () => { sel.dataset.previousValue = sel.value; });
});

window.addEventListener('pageshow', function (e) {
    if (e.persisted) window.location.reload();
});

document.getElementById('walkInForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const res = await fetch('{{ route("appointments.walkin.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (data.success) { closeWalkIn(); location.reload(); }
        else { alert(data.message || 'Failed to create appointment.'); }
    } catch (err) { console.error(err); alert('Something went wrong.'); }
});

document.getElementById('closePaymentModal').addEventListener('click', closePayment);
document.getElementById('cancelPayment').addEventListener('click', closePayment);
</script>

@endsection