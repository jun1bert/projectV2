@extends('layouts.app')
@extends('layouts.dashboard')
@section('header', 'Service Management')
@section('subheader', 'Manage pricing, duration, and offerings')
@section('content')

{{-- ===================== TOOLBAR ===================== --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <p class="text-sm text-gray-500">{{ $services->count() }} service{{ $services->count() !== 1 ? 's' : '' }} available</p>

    <button onclick="openCreateModal()"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Service
    </button>
</div>

{{-- ===================== SERVICE GRID ===================== --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">

@forelse($services as $s)

<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 flex flex-col justify-between">
    <div>
        <h3 class="font-semibold text-gray-900 text-sm">{{ $s->name }}</h3>
        <div class="mt-1.5 flex items-center gap-2 text-sm">
            <span class="font-semibold text-[#c8a96a]">₱{{ number_format($s->price, 2) }}</span>
            <span class="text-gray-300">•</span>
            <span class="text-gray-500 text-xs">{{ $s->duration }} mins</span>
        </div>
        @if($s->description)
        <p class="text-xs text-gray-400 mt-3 leading-relaxed">{{ $s->description }}</p>
        @endif
    </div>

    <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-50">
        <button onclick="openEditModal({{ $s }})"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </button>

        <button onclick="openDeleteModal({{ $s->id }})"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete
        </button>
    </div>
</div>

@empty
<div class="col-span-full text-center py-16">
    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
    </svg>
    <p class="text-gray-400 text-sm">No services yet.</p>
</div>
@endforelse

</div>

{{-- ===================== CREATE MODAL ===================== --}}
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeCreateModal()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Add Service</h2>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('services.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Service name</label>
                    <input name="name" required placeholder="e.g. Deep Tissue Massage"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Price (₱)</label>
                        <input name="price" type="number" step="0.01" required placeholder="0.00"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Duration (mins)</label>
                        <input name="duration" type="number" required placeholder="60"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Brief description…"
                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition resize-none"></textarea>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeCreateModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Save Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== EDIT MODAL ===================== --}}
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Edit Service</h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editForm" method="POST" class="px-6 py-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Service name</label>
                    <input id="edit_name" name="name" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Price (₱)</label>
                        <input id="edit_price" name="price" type="number" step="0.01" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Duration (mins)</label>
                        <input id="edit_duration" name="duration" type="number" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea id="edit_description" name="description" rows="3"
                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition resize-none"></textarea>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeEditModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== DELETE MODAL ===================== --}}
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Delete Service?</h2>
                        <p class="text-xs text-gray-400 mt-0.5">This action cannot be undone.</p>
                    </div>
                </div>
                <div class="flex gap-2 mt-5">
                    <button onclick="closeDeleteModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== JS ===================== --}}
<script>
function openCreateModal()  { document.getElementById('createModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeCreateModal() { document.getElementById('createModal').classList.add('hidden');    document.body.style.overflow = ''; }

function openEditModal(service) {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('editForm').action            = `/services/${service.id}`;
    document.getElementById('edit_name').value            = service.name;
    document.getElementById('edit_price').value           = service.price;
    document.getElementById('edit_duration').value        = service.duration;
    document.getElementById('edit_description').value     = service.description ?? '';
}
function closeEditModal()   { document.getElementById('editModal').classList.add('hidden');      document.body.style.overflow = ''; }

function openDeleteModal(id) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('deleteForm').action = `/services/${id}`;
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden');    document.body.style.overflow = ''; }
</script>

@endsection