
@extends('layouts.dashboard')
@section('header', 'Inventory Management')
@section('subheader', 'Manage and update all inventory items, stock levels, and pricing')

@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">

    <div class="flex flex-col sm:flex-row gap-2 flex-1">

        <div class="relative flex-1 min-w-0 sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35m0 0A7 7 0 1110 3a7 7 0 016.65 13.65z"/>
            </svg>
            <input id="searchInput" onkeyup="filterTable()" placeholder="Search product…"
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
        </div>

        <select id="stockFilter" onchange="filterTable()"
                class="w-full sm:w-44 px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
            <option value="all">All Stock</option>
            <option value="low">Low Stock</option>
            <option value="ok">OK Stock</option>
        </select>

    </div>

    <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Product
    </button>

</div>

{{-- ===================== SUMMARY CARDS ===================== --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-xs text-gray-500 mb-1">Total Products</p>
        <p class="text-2xl font-bold text-gray-900">{{ $products->count() }}</p>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <p class="text-xs text-gray-500 mb-1">Low Stock Items</p>
        <p class="text-2xl font-bold text-red-500">{{ $products->where('current_stock', '<=', 5)->count() }}</p>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm col-span-2 lg:col-span-1">
        <p class="text-xs text-gray-500 mb-1">Inventory Value</p>
        <p class="text-2xl font-bold text-[#c8a96a]">
            ₱{{ number_format($products->sum(fn($p) => $p->current_stock * $p->cost_price), 2) }}
        </p>
    </div>

</div>

{{-- ===================== TOAST ===================== --}}
<div id="toastContainer" class="fixed top-5 right-5 space-y-2 z-50"></div>

{{-- ===================== DESKTOP TABLE ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm" id="desktopTable">

    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <span class="text-sm font-semibold text-gray-900">Product List</span>
        <span class="text-xs text-gray-400">{{ $products->count() }} items</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                    <th class="px-5 py-3.5 text-left">Product</th>
                    <th class="px-5 py-3.5 text-left">Stock</th>
                    <th class="px-5 py-3.5 text-left">Unit</th>
                    <th class="px-5 py-3.5 text-left">Price</th>
                    <th class="px-5 py-3.5 text-left">Status</th>
                    <th class="px-5 py-3.5 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($products as $p)
                <tr class="product-row hover:bg-[#faf7f2] transition-colors"
                    data-name="{{ strtolower($p->name) }}"
                    data-stock="{{ $p->current_stock <= 5 ? 'low' : 'ok' }}">

                    <td class="px-5 py-4 font-medium text-gray-900">{{ $p->name }}</td>

                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $p->current_stock <= 5 ? 'bg-red-50 text-red-600 ring-1 ring-red-200' : 'bg-gray-100 text-gray-700' }}">
                            {{ $p->current_stock }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-gray-500">{{ $p->unit ?? '—' }}</td>

                    <td class="px-5 py-4 font-medium text-[#c8a96a]">
                        ₱{{ number_format($p->cost_price ?? 0, 2) }}
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $p->current_stock <= 5
                                ? 'bg-red-50 text-red-600 ring-1 ring-red-200'
                                : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' }}">
                            {{ $p->current_stock <= 5 ? 'Low Stock' : 'OK' }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex gap-2">
                            <button type="button"
                                    onclick="openEditModal(@js(['id' => $p->id, 'name' => $p->name, 'unit' => $p->unit, 'cost_price' => $p->cost_price, 'current_stock' => $p->current_stock]))"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <button type="button"
                                    onclick="openDetails({{ $p->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Details
                            </button>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-gray-400 text-sm">No products yet.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===================== MOBILE CARDS ===================== --}}
<div class="grid gap-3" id="mobileCards">

@forelse($products as $p)
<div class="product-row bg-white border border-gray-100 rounded-2xl p-4 shadow-sm"
     data-name="{{ strtolower($p->name) }}"
     data-stock="{{ $p->current_stock <= 5 ? 'low' : 'ok' }}">

    <div class="flex justify-between items-start gap-2 mb-3">
        <div>
            <p class="font-semibold text-gray-900 text-sm">{{ $p->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $p->unit ?? '—' }}</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shrink-0
            {{ $p->current_stock <= 5
                ? 'bg-red-50 text-red-600 ring-1 ring-red-200'
                : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' }}">
            {{ $p->current_stock <= 5 ? 'Low Stock' : 'OK' }}
        </span>
    </div>

    <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
        <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-[#c8a96a]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Stock: <span class="font-medium {{ $p->current_stock <= 5 ? 'text-red-500' : 'text-gray-700' }}">{{ $p->current_stock }}</span>
        </span>
        <span class="font-semibold text-[#c8a96a]">₱{{ number_format($p->cost_price ?? 0, 2) }}</span>
    </div>

    <div class="border-t border-gray-100 pt-3 flex gap-2">
        <button type="button"
                onclick="openEditModal(@js(['id' => $p->id, 'name' => $p->name, 'unit' => $p->unit, 'cost_price' => $p->cost_price, 'current_stock' => $p->current_stock]))"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </button>
        <button type="button"
                onclick="openDetails({{ $p->id }})"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Details
        </button>
    </div>

</div>
@empty
<div class="text-center py-16 text-gray-400 text-sm">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    No products yet.
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

{{-- ===================== CREATE MODAL ===================== --}}
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeCreateModal()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Add Product</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Create new inventory item</p>
                </div>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="/inventory" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Product Name</label>
                    <input name="name" required placeholder="e.g. Massage Oil"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Unit <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input name="unit" placeholder="e.g. ml, pcs, bottle"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Cost Price (₱)</label>
                        <input name="cost_price" type="number" step="0.01" required placeholder="0.00"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Initial Stock</label>
                        <input name="initial_stock" type="number" required placeholder="0"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeCreateModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Save Product
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ===================== EDIT MODAL ===================== --}}
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Edit Product</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Update details & adjust stock</p>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" id="editForm" class="px-6 py-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Product Name</label>
                    <input id="edit_name" name="name"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Unit <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input id="edit_unit" name="unit"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Cost Price (₱)</label>
                    <input id="edit_cost" name="cost_price" type="number" step="0.01"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Set New Stock Quantity</label>
                    <input id="edit_stock" name="stock_qty" type="number"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    <p class="text-xs text-gray-400 mt-1.5">
                        Current stock: <span id="current_stock_label" class="font-medium text-gray-600"></span>
                    </p>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                    <button type="button" onclick="confirmDeleteInline()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Product
                    </button>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                            Save Changes
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ===================== DETAILS MODAL ===================== --}}
<div id="detailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetails()"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Product Details</h2>
                <button onclick="closeDetails()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div id="productInfo"></div>

                <div>
                    <h3 class="text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide mb-2">Stock History</h3>
                    <div id="logList" class="space-y-2"></div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ===================== SCRIPTS ===================== --}}
<script>

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const stock  = document.getElementById('stockFilter').value;

    document.querySelectorAll('.product-row').forEach(row => {
        const matchSearch = row.dataset.name.includes(search);
        const matchStock  = stock === 'all' || row.dataset.stock === stock;
        row.style.display = (matchSearch && matchStock) ? '' : 'none';
    });
}

function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function openEditModal(p) {
    document.getElementById('edit_name').value          = p.name;
    document.getElementById('edit_unit').value          = p.unit ?? '';
    document.getElementById('edit_cost').value          = p.cost_price ?? 0;
    document.getElementById('edit_stock').value         = p.current_stock;
    document.getElementById('current_stock_label').innerText = p.current_stock;
    document.getElementById('editForm').action          = `/inventory/${p.id}`;
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function openDetails(id) {
    document.getElementById('detailsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('productInfo').innerHTML = '<p class="text-sm text-gray-400">Loading…</p>';
    document.getElementById('logList').innerHTML = '';

    fetch(`/inventory/${id}`)
        .then(r => r.json())
        .then(data => {
            const p = data.product;

            document.getElementById('productInfo').innerHTML = `
                <div class="bg-[#faf7f2] border border-[#c8a96a]/20 rounded-xl p-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">Name</p>
                        <p class="font-semibold text-gray-900 mt-0.5">${p.name}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Unit</p>
                        <p class="font-medium text-gray-700 mt-0.5">${p.unit ?? '—'}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Current Stock</p>
                        <p class="font-semibold mt-0.5 ${p.current_stock <= 5 ? 'text-red-500' : 'text-emerald-600'}">${p.current_stock}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Cost Price</p>
                        <p class="font-semibold text-[#c8a96a] mt-0.5">₱${parseFloat(p.cost_price ?? 0).toFixed(2)}</p>
                    </div>
                </div>
            `;

            if (!data.logs.length) {
                document.getElementById('logList').innerHTML = '<p class="text-xs text-gray-400">No stock history yet.</p>';
                return;
            }

            document.getElementById('logList').innerHTML = data.logs.map(l => `
                <div class="flex items-start justify-between gap-2 px-4 py-3 bg-white border border-gray-100 rounded-xl text-sm">
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mr-2
                            ${l.type === 'in' ? 'bg-emerald-50 text-emerald-700' : l.type === 'out' ? 'bg-red-50 text-red-600' : 'bg-[#c8a96a]/10 text-[#8a6a30]'}">
                            ${l.type.toUpperCase()}
                        </span>
                        <span class="text-gray-400 text-xs">${l.note ?? ''}</span>
                    </div>
                    <span class="font-semibold text-gray-800 shrink-0">${l.quantity > 0 ? '+' : ''}${l.quantity}</span>
                </div>
            `).join('');
        });
}
function closeDetails() {
    document.getElementById('detailsModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function confirmDeleteInline() {
    if (!confirm('Delete this product? This cannot be undone.')) return;
    const id = document.getElementById('editForm').action.split('/').pop();
    fetch(`/inventory/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'DELETE',
            'Content-Type': 'application/json'
        }
    }).then(() => location.reload());
}

</script>

@endsection