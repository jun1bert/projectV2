
@extends('layouts.dashboard')
@section('header', 'Commission Dashboard')
@section('subheader', 'Staff earnings & payout overview')

@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="flex justify-end mb-6">
    @if(in_array(auth()->user()->role, ['admin','management']))
        <a href="/commission-rules"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#3a2a22] hover:bg-[#2b1f1a] text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Commission Rules
        </a>
    @endif
</div>

{{-- ===================== SUMMARY CARDS ===================== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-xs text-gray-500 mb-1">Total Earnings</p>
        <p class="text-xl font-bold text-gray-900">₱{{ number_format($totalEarnings, 2) }}</p>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-xs text-gray-500 mb-1">This Month</p>
        <p class="text-xl font-bold text-[#c8a96a]">₱{{ number_format($monthlyEarnings, 2) }}</p>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-xs text-gray-500 mb-1">Pending Payout</p>
        <p class="text-xl font-bold text-amber-600">₱{{ number_format($pendingPayout, 2) }}</p>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-xs text-gray-500 mb-1">Paid Out</p>
        <p class="text-xl font-bold text-emerald-600">₱{{ number_format($paid, 2) }}</p>
    </div>

</div>

{{-- ===================== DESKTOP TABLE ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm" id="desktopTable">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                <th class="px-5 py-3.5 text-left">Staff</th>
                <th class="px-5 py-3.5 text-left">Services Completed</th>
                <th class="px-5 py-3.5 text-left">Total Earnings</th>
                <th class="px-5 py-3.5 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
        @forelse($staffEarnings as $staff)
            <tr class="hover:bg-[#faf7f2] transition-colors">
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#c8a96a]/20 text-[#8a6a30] flex items-center justify-center text-xs font-semibold shrink-0">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </div>
                        <span class="font-medium text-gray-900">{{ $staff->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-4 text-gray-600">{{ $staff->commissions_count }}</td>
                <td class="px-5 py-4 font-semibold text-[#c8a96a]">
                    ₱{{ number_format($staff->total_earnings ?? 0, 2) }}
                </td>
                <td class="px-5 py-4">
                    <button onclick="openCommissionModal({{ $staff->id }}, '{{ addslashes($staff->name) }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View Details
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-5 py-16 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-400 text-sm">No commission data found.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- ===================== MOBILE CARDS ===================== --}}
<div class="grid gap-3" id="mobileCards">

@forelse($staffEarnings as $staff)
<div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
    <div class="flex justify-between items-start gap-2 mb-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-[#c8a96a]/20 text-[#8a6a30] flex items-center justify-center text-sm font-semibold shrink-0">
                {{ strtoupper(substr($staff->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">{{ $staff->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $staff->commissions_count }} services completed</p>
            </div>
        </div>
        <p class="font-bold text-[#c8a96a] text-sm shrink-0">₱{{ number_format($staff->total_earnings ?? 0, 2) }}</p>
    </div>
    <div class="border-t border-gray-100 pt-3">
        <button onclick="openCommissionModal({{ $staff->id }}, '{{ addslashes($staff->name) }}')"
                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            View Details
        </button>
    </div>
</div>
@empty
<div class="text-center py-16 text-gray-400 text-sm">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
    </svg>
    No commission data found.
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

{{-- ===================== COMMISSION DETAIL MODAL ===================== --}}
<div id="commissionModal"
     class="hidden fixed inset-0 z-50 overflow-y-auto"
     role="dialog" aria-modal="true">

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>

        <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 id="modalStaffName" class="text-base font-semibold text-gray-900">Staff</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">

                {{-- Summary --}}
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="bg-[#faf7f2] border border-gray-100 rounded-xl p-3 text-sm">
                        <p class="text-xs text-gray-500 mb-1">Total</p>
                        <p id="m_total" class="font-bold text-gray-900"></p>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-sm">
                        <p class="text-xs text-amber-600 mb-1">Pending</p>
                        <p id="m_pending" class="font-bold text-amber-700"></p>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 text-sm">
                        <p class="text-xs text-emerald-600 mb-1">Paid</p>
                        <p id="m_paid" class="font-bold text-emerald-700"></p>
                    </div>
                </div>

                {{-- Detail table --}}
                <div class="max-h-96 overflow-y-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#3a2a22]/5 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                                <th class="px-4 py-3 text-left">Service</th>
<th class="px-4 py-3 text-left">Date</th>
<th class="px-4 py-3 text-left">Amount</th>
<th class="px-4 py-3 text-left">Commission</th>
                            </tr>
                        </thead>
                        <tbody id="modalBody" class="divide-y divide-gray-50"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ===================== SCRIPTS ===================== --}}
<script>

let currentStaffId   = null;
let currentStaffName = null;

async function openCommissionModal(id, name) {
    currentStaffId   = id;
    currentStaffName = name;

    document.getElementById('commissionModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('modalStaffName').innerText = name;
    document.getElementById('modalBody').innerHTML = `
        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No commission records yet.</td></tr>
    `;

    const res  = await fetch(`/commissions/${id}/details`);
    const data = await res.json();

    document.getElementById('m_total').innerText   = '₱' + parseFloat(data.total).toFixed(2);
    document.getElementById('m_pending').innerText = '₱' + parseFloat(data.pending).toFixed(2);
    document.getElementById('m_paid').innerText    = '₱' + parseFloat(data.paid).toFixed(2);

    if (!data.items.length) {
        document.getElementById('modalBody').innerHTML = `
            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No commission records yet.</td></tr>
        `;
        return;
    }

    document.getElementById('modalBody').innerHTML = data.items.map(item => `
    <tr class="hover:bg-[#faf7f2] transition-colors">
        <td class="px-4 py-3 text-gray-800">${item.service}</td>
        <td class="px-4 py-3 text-xs text-gray-500">${item.date ?? ''} ${item.time ?? ''}</td>
        <td class="px-4 py-3 text-gray-700">₱${parseFloat(item.amount).toFixed(2)}</td>
        <td class="px-4 py-3 font-medium text-[#c8a96a]">₱${parseFloat(item.commission).toFixed(2)}</td>
    </tr>
`).join('');
}

async function markPaid(id) {
    try {
        const res  = await fetch(`/commissions/${id}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();

        if (data.success) {
            openCommissionModal(currentStaffId, currentStaffName);
        } else {
            alert(data.message || 'Failed to update commission');
        }
    } catch (err) {
        console.error(err);
        alert('Server error');
    }
}

function closeModal() {
    document.getElementById('commissionModal').classList.add('hidden');
    document.body.style.overflow = '';
}

</script>

@endsection