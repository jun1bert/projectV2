@extends('layouts.app')
@extends('layouts.dashboard')
@section('content')
@section('header', 'Inventory Management')
@section('subheader', 'Manage and update all inventory items, stock levels, and pricing')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <button onclick="openCreateModal()"
            class="px-5 py-2.5 rounded-xl bg-black text-white hover:bg-gray-900 transition">
            Add Product
        </button>

    </div>

    <!-- SUMMARY -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <div class="bg-white rounded-2xl p-5 shadow-sm border">
            <p class="text-xs text-gray-500">Total Products</p>
            <p class="text-2xl font-semibold mt-1">{{ $products->count() }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border">
            <p class="text-xs text-gray-500">Low Stock Items</p>
            <p class="text-2xl font-semibold mt-1 text-red-600">
                {{ $products->where('current_stock', '<=', 5)->count() }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border">
            <p class="text-xs text-gray-500">Inventory Value</p>
            <p class="text-2xl font-semibold mt-1">
                ₱{{ number_format($products->sum(fn($p) => $p->current_stock * $p->cost_price), 2) }}
            </p>
        </div>

    </div>

    <!-- TOAST -->
    <div id="toastContainer" class="fixed top-5 right-5 space-y-2 z-50"></div>

    <!-- SEARCH -->
    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">

        <input id="searchInput" onkeyup="filterTable()"
            placeholder="Search product..."
            class="w-full md:w-1/3 border rounded-xl p-3">

        <select id="stockFilter" onchange="filterTable()"
            class="w-full md:w-48 border rounded-xl p-3">

            <option value="all">All Stock</option>
            <option value="low">Low Stock</option>
            <option value="ok">OK Stock</option>

        </select>

    </div>

    <!-- TABLE -->
<!-- TABLE -->
<div class="bg-white rounded-2xl border shadow-sm overflow-hidden">

    <div class="p-4 border-b flex justify-between">
        <span class="font-semibold text-gray-700">Product List</span>
        <span class="text-xs text-gray-400">{{ $products->count() }} items</span>
    </div>

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="p-4 text-left">Product</th>
                    <th class="p-4 text-left">Stock</th>
                    <th class="p-4 text-left">Unit</th>
                    <th class="p-4 text-left">Price</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">

            @foreach($products as $p)

                <tr class="hover:bg-gray-50">

                    <td class="p-4 font-medium">{{ $p->name }}</td>

                    <td class="p-4">
                        <span class="px-2 py-1 rounded-full text-xs
                            {{ $p->current_stock <= 5 ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-700' }}">
                            {{ $p->current_stock }}
                        </span>
                    </td>

                    <td class="p-4 text-gray-600">{{ $p->unit ?? '-' }}</td>

                    <td class="p-4 font-medium">
                        ₱{{ number_format($p->cost_price ?? 0, 2) }}
                    </td>

                    <td class="p-4">
                        <span class="{{ $p->current_stock <= 5 ? 'text-red-600' : 'text-green-600' }} text-xs font-semibold">
                            {{ $p->current_stock <= 5 ? 'Low Stock' : 'OK' }}
                        </span>
                    </td>

                    <td class="p-4">
                        <div class="flex gap-2">

                            <button type="button"
                                onclick="openEditModal(@js([
                                    'id' => $p->id,
                                    'name' => $p->name,
                                    'unit' => $p->unit,
                                    'cost_price' => $p->cost_price,
                                    'initial_stock' => $p->current_stock
                                ]))"
                                class="px-3 py-1.5 text-xs border rounded-lg">
                                Edit
                            </button>

                            <button type="button"
                                onclick="openDetails({{ $p->id }})"
                                class="px-3 py-1.5 text-xs border rounded-lg">
                                Details
                            </button>

                        </div>
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

<!-- CREATE -->
<div id="createModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden">

        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold">Add Product</h2>
            <p class="text-xs text-gray-500">Create new inventory item</p>
        </div>

        <form method="POST" action="/inventory" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="text-xs text-gray-600">Product Name</label>
                <input name="name" class="w-full border rounded-lg p-2.5 text-sm mt-1" required>
            </div>

            <div>
                <label class="text-xs text-gray-600">Unit</label>
                <input name="unit" class="w-full border rounded-lg p-2.5 text-sm mt-1">
            </div>

            <div>
                <label class="text-xs text-gray-600">Cost Price</label>
                <input name="cost_price" type="number" step="0.01"
                    class="w-full border rounded-lg p-2.5 text-sm mt-1" required>
            </div>

            <div>
                <label class="text-xs text-gray-600">Initial Stock</label>
                <input name="initial_stock" type="number"
                    class="w-full border rounded-lg p-2.5 text-sm mt-1" required>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">

                <button type="button" onclick="closeCreateModal()"
                    class="px-3 py-2 text-xs border rounded-lg">
                    Cancel
                </button>

                <button class="px-3 py-2 text-xs bg-black text-white rounded-lg">
                    Save
                </button>

            </div>

        </form>

    </div>
</div>

<!-- EDIT (with delete inside) -->
<div id="editModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden">

        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold">Edit Product</h2>
            <p class="text-xs text-gray-500">Manage product + inventory adjustment</p>
        </div>

        <form method="POST" id="editForm" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <!-- PRODUCT FIELDS -->
            <div>
                <label class="text-xs text-gray-600">Product Name</label>
                <input id="edit_name" name="name"
                    class="w-full border rounded-lg p-2.5 text-sm mt-1">
            </div>

            <div>
                <label class="text-xs text-gray-600">Unit</label>
                <input id="edit_unit" name="unit"
                    class="w-full border rounded-lg p-2.5 text-sm mt-1">
            </div>

            <div>
                <label class="text-xs text-gray-600">Cost Price</label>
                <input id="edit_cost" name="cost_price"
                    class="w-full border rounded-lg p-2.5 text-sm mt-1">
            </div>

            <hr class="my-2">

            <!-- STOCK ADJUSTMENT -->
            <div>
                <label class="text-xs text-gray-600">Adjust Stock (Set New Value)</label>
                <input id="edit_stock" name="stock_qty" type="number"
                    class="w-full border rounded-lg p-2.5 text-sm mt-1">
                <p class="text-[11px] text-gray-400 mt-1">
                    Current stock: <span id="current_stock_label"></span>
                </p>
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-between pt-3 border-t">

                <button type="button"
    onclick="confirmDeleteInline()"
    class="px-3 py-2 text-xs text-red-600 border rounded-lg">
    Delete Product
</button>

                <div class="flex gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-3 py-2 text-xs border rounded-lg">
                        Cancel
                    </button>

                    <button class="px-3 py-2 text-xs bg-blue-600 text-white rounded-lg">
                        Save Changes
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>

<!-- DELETE -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl p-6 w-full max-w-md">

        <h2 class="font-semibold text-red-600">Delete Product?</h2>
        <p class="text-sm text-gray-500 mt-2">This action cannot be undone.</p>

        <form id="deleteForm" method="POST" class="mt-4">
            @csrf
            @method('DELETE')

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDeleteModal()" class="px-3 py-2 border rounded">
                    Cancel
                </button>
                <button class="px-3 py-2 bg-red-600 text-white rounded">
                    Delete
                </button>
            </div>

        </form>

    </div>
</div>

<!-- STOCK -->
<div id="stockModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden">

        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 id="stockTitle" class="font-semibold">Stock</h2>
        </div>

        <form method="POST" action="/inventory/stock-move" class="p-6 space-y-4">
            @csrf

            <input type="hidden" id="stock_product_id" name="product_id">
            <input type="hidden" id="stock_type" name="type">

            <input name="qty" type="number" class="w-full border p-2 rounded">
            <input name="note" class="w-full border p-2 rounded">

            <button id="stockBtn" class="w-full py-2 rounded text-white bg-green-600">
                Confirm
            </button>

        </form>

    </div>
</div>

<!-- DETAILS -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl overflow-hidden">

        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="font-semibold">Product Details</h2>
        </div>

        <div class="p-6 space-y-4">

            <div id="productInfo"></div>
            <div id="logList"></div>

        </div>

        <div class="px-6 py-4 border-t flex justify-end">
            <button onclick="closeDetails()" class="px-4 py-2 border rounded">
                Close
            </button>
        </div>

    </div>
</div>

<script>

let currentEditId = null;

function openCreateModal(){
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal(){ document.getElementById('createModal').classList.add('hidden'); }

function openEditModal(p){
    document.getElementById('edit_name').value = p.name;
    document.getElementById('edit_unit').value = p.unit ?? '';
    document.getElementById('edit_cost').value = p.cost_price ?? 0;

    document.getElementById('edit_stock').value = p.current_stock;
    document.getElementById('current_stock_label').innerText = p.current_stock;

    document.getElementById('editForm').action = `/inventory/${p.id}`;
    document.getElementById('editModal').classList.remove('hidden');
}

function openStockModal(id, type){
    document.getElementById('stock_product_id').value = id;
    document.getElementById('stock_type').value = type;

    const title = document.getElementById('stockTitle');
    const btn = document.getElementById('stockBtn');

    if(type === 'in'){
        title.innerText = 'Stock In';
        btn.className = 'w-full py-2 rounded text-white bg-green-600';
        btn.innerText = 'Add Stock';
    }

    if(type === 'out'){
        title.innerText = 'Stock Out';
        btn.className = 'w-full py-2 rounded text-white bg-red-600';
        btn.innerText = 'Remove Stock';
    }

    if(type === 'adjust'){
        title.innerText = 'Adjust Stock';
        btn.className = 'w-full py-2 rounded text-white bg-blue-600';
        btn.innerText = 'Adjust';
    }

    document.getElementById('stockModal').classList.remove('hidden');
}

function openDetails(id){
    fetch(`/inventory/${id}`)
        .then(r => r.json())
        .then(data => {

            const p = data.product;

            document.getElementById('productInfo').innerHTML = `
                <div><b>${p.name}</b></div>
                <div>Stock: ${p.current_stock}</div>
                <div>Unit: ${p.unit ?? '-'}</div>
                <div>Price: ₱${p.cost_price ?? 0}</div>
            `;

            document.getElementById('logList').innerHTML =
                data.logs.map(l => `
                    <div class="border p-2 rounded">
                        <div><b>${l.type}</b> ${l.quantity}</div>
                        <div class="text-xs text-gray-500">${l.note ?? ''}</div>
                    </div>
                `).join('');

            document.getElementById('detailsModal').classList.remove('hidden');
        });
}
function openDeleteFromEdit(){
    const id = document.getElementById('editForm').action.split('/').pop();
    openDeleteModal(id);
}
function closeEditModal(){ document.getElementById('editModal').classList.add('hidden'); }
function closeCreateModal(){ document.getElementById('createModal').classList.add('hidden'); }
function closeStockModal(){ document.getElementById('stockModal').classList.add('hidden'); }
function closeDeleteModal(){ document.getElementById('deleteModal').classList.add('hidden'); }
function closeDetails(){ document.getElementById('detailsModal').classList.add('hidden'); }
function confirmDeleteInline(){
    if(!confirm('Delete this product? This cannot be undone.')) return;

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