@extends('layouts.app')
@extends('layouts.dashboard')
@section('header', 'Commission Dashboard')
@section('subheader', 'Staff earnings & payout overview')

@section('content')

<!-- ACTION BAR -->
<div class="flex justify-end mb-6">
    @if(in_array(auth()->user()->role, ['admin','management']))
        <a href="/commission-rules"
           class="h-10 flex items-center px-4 bg-black text-white text-sm rounded-lg hover:bg-gray-800">
            ⚙ Commission Rules
        </a>
    @endif
</div>

<!-- SUMMARY -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="p-4 bg-white rounded-2xl shadow">
        <p class="text-xs text-gray-500">Total Earnings</p>
        <p class="text-xl font-bold">₱{{ number_format($totalEarnings, 2) }}</p>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow">
        <p class="text-xs text-gray-500">This Month</p>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($monthlyEarnings, 2) }}</p>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow">
        <p class="text-xs text-gray-500">Pending Payout</p>
        <p class="text-xl font-bold text-yellow-600">₱{{ number_format($pendingPayout, 2) }}</p>
    </div>

    <div class="p-4 bg-white rounded-2xl shadow">
        <p class="text-xs text-gray-500">Paid</p>
        <p class="text-xl font-bold text-blue-600">₱{{ number_format($paid, 2) }}</p>
    </div>

</div>

<!-- TABLE -->
<div class="bg-white rounded-2xl shadow overflow-hidden">

<table class="w-full text-sm">
    <thead class="bg-gray-50 text-left">
        <tr>
            <th class="p-4">Staff</th>
            <th class="p-4">Services Completed</th>
            <th class="p-4">Total Earnings</th>
            <th class="p-4">Actions</th>
        </tr>
    </thead>

    <tbody>
    @forelse($staffEarnings as $staff)
        <tr class="border-b hover:bg-gray-50">

            <td class="p-4 font-medium">{{ $staff->name }}</td>
            <td class="p-4">{{ $staff->commissions_count }}</td>

            <td class="p-4 text-green-700 font-semibold">
                ₱{{ number_format($staff->total_earnings ?? 0, 2) }}
            </td>

            <td class="p-4">
                <button onclick="openCommissionModal({{ $staff->id }}, '{{ $staff->name }}')"
                        class="text-blue-600 text-sm hover:underline">
                    View Details
                </button>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="4" class="p-4 text-center text-gray-500">
                No commission data found.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</div>

<!-- MODAL -->
<div id="commissionModal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-3xl rounded-xl shadow-lg p-5">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold" id="modalStaffName">Staff</h2>
            <button onclick="closeModal()" class="text-gray-500">✕</button>
        </div>

        <!-- SUMMARY -->
        <div class="grid grid-cols-3 gap-3 mb-4 text-sm">

            <div class="p-3 bg-gray-100 rounded">
                <p class="text-xs">Total</p>
                <p id="m_total" class="font-bold"></p>
            </div>

            <div class="p-3 bg-yellow-100 rounded">
                <p class="text-xs">Pending</p>
                <p id="m_pending" class="font-bold"></p>
            </div>

            <div class="p-3 bg-green-100 rounded">
                <p class="text-xs">Paid</p>
                <p id="m_paid" class="font-bold"></p>
            </div>

        </div>

        <!-- TABLE -->
        <div class="max-h-96 overflow-y-auto border rounded">

            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2">Service</th>
                        <th class="p-2">Date</th>
                        <th class="p-2">Amount</th>
                        <th class="p-2">Commission</th>
                        <th class="p-2">Status</th>
                        <th class="p-2">Action</th>
                    </tr>
                </thead>

                <tbody id="modalBody"></tbody>
            </table>

        </div>

    </div>
</div>
<script>

let currentStaffId = null;
let currentStaffName = null;

async function openCommissionModal(id, name) {

    currentStaffId = id;
    currentStaffName = name;

    document.getElementById('commissionModal').classList.remove('hidden');
    document.getElementById('modalStaffName').innerText = name;

    const res = await fetch(`/commissions/${id}/details`);
    const data = await res.json();

    document.getElementById('m_total').innerText = '₱' + data.total.toFixed(2);
    document.getElementById('m_pending').innerText = '₱' + data.pending.toFixed(2);
    document.getElementById('m_paid').innerText = '₱' + data.paid.toFixed(2);

    let html = '';

    data.items.forEach(item => {
        html += `
            <tr class="border-b">

                <td class="p-2">${item.service}</td>
                <td class="p-2">${item.date ?? ''} ${item.time ?? ''}</td>
                <td class="p-2">₱${parseFloat(item.amount).toFixed(2)}</td>
                <td class="p-2">₱${parseFloat(item.commission).toFixed(2)}</td>

                <td class="p-2">
                    <span class="text-xs px-2 py-1 rounded bg-gray-100">
                        ${item.status}
                    </span>
                </td>

                <td class="p-2">
                    ${item.status === 'pending' ? `
                        <button onclick="markPaid(${item.id})"
                            class="text-blue-600 text-xs hover:underline">
                            Mark Paid
                        </button>
                    ` : ''}
                </td>

            </tr>
        `;
    });

    document.getElementById('modalBody').innerHTML = html;
}

async function markPaid(id) {
    try {
        const res = await fetch(`/commissions/${id}/mark-paid`, {
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
}

</script>
@endsection