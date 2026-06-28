@extends('layouts.dashboard')

@section('header', 'Service Management')
@section('subheader', 'Manage pricing, duration, and offerings')
<title><?= config('app.name') ?> | Service Management</title>
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

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-xl font-semibold text-[var(--ink)]">Service Catalog</h3>
        <p class="mt-1 text-sm text-[var(--muted)]">{{ $services->count() }} service{{ $services->count() !== 1 ? 's' : '' }} available</p>
    </div>

    <button type="button" onclick="openCreateModal()"
            class="theme-button inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
        Add Service
    </button>
</div>

@if(session('success'))
<div role="status" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
    {{ session('success') }}
</div>
@endif

<datalist id="serviceCategoryOptions">
    @foreach($categories as $category)
        <option value="{{ $category }}"></option>
    @endforeach
</datalist>

@php
    $servicesByCategory = $services->groupBy('category');
@endphp

@forelse($servicesByCategory as $category => $categoryServices)
    <details class="theme-card mb-5 overflow-hidden rounded-2xl" {{ $loop->first ? 'open' : '' }}>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 marker:hidden">
            <div>
                <h3 class="text-lg font-semibold text-[var(--ink)]">{{ $category }}</h3>
                <p class="mt-1 text-xs text-[var(--muted)]">
                    {{ $categoryServices->count() }} service{{ $categoryServices->count() === 1 ? '' : 's' }}
                    &bull; {{ $categoryServices->where('is_active', true)->count() }} active
                </p>
            </div>
            <span class="rounded-full bg-[rgba(164,141,120,.14)] px-3 py-1 text-xs font-semibold text-[var(--desert-rock)]">View</span>
        </summary>

        <div class="grid gap-4 border-t border-[rgba(164,141,120,.16)] p-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($categoryServices as $service)
                <article class="flex min-h-[190px] flex-col justify-between rounded-2xl border border-[rgba(164,141,120,.18)] bg-[rgba(250,249,246,.72)] p-5">
                    <div>
                        <h4 class="text-base font-semibold text-[var(--ink)]">{{ $service->name }}</h4>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                            <span class="font-semibold text-[var(--desert-rock)]">PHP {{ number_format($service->price, 2) }}</span>
                            <span class="h-1 w-1 rounded-full bg-[var(--soft-sandstone)]"></span>
                            <span class="text-[var(--muted)]">{{ $service->duration ? $service->duration.' mins' : 'No fixed duration' }}</span>
                            @if($service->session_count > 1)
                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ $service->session_count }} sessions</span>
                            @endif
                            @if($service->requires_consent)
                                <span class="rounded-full bg-[rgba(164,141,120,.14)] px-2 py-0.5 text-xs font-semibold text-[var(--ink)]">Consent required</span>
                            @endif
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $service->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-600' }}">
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        @if($service->description)
                            <p class="mt-4 text-sm leading-relaxed text-[var(--muted)]">{{ $service->description }}</p>
                        @endif
                    </div>

                    <div class="mt-5 flex justify-end gap-2 border-t border-[rgba(164,141,120,.16)] pt-4">
                        <button type="button" onclick="openEditModal({{ $service }})" class="muted-button rounded-xl px-3 py-2 text-xs font-semibold transition">Edit</button>
                        <button type="button" onclick="openDeleteModal({{ $service->id }})" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 ring-1 ring-rose-100 transition hover:bg-rose-100">Delete</button>
                    </div>
                </article>
            @endforeach
        </div>
    </details>
@empty
    <div class="theme-card col-span-full rounded-2xl px-5 py-16 text-center text-sm text-[var(--muted)]">
        No services yet.
    </div>
@endforelse

<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeCreateModal()" aria-label="Close create modal"></button>

        <div class="theme-card relative w-full max-w-md overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <h2 class="text-lg font-semibold text-[var(--ink)]">Add Service</h2>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeCreateModal()">Close</button>
            </div>

            <form method="POST" action="{{ route('services.store') }}" class="space-y-4 px-5 py-5">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Service name</label>
                    <input name="name" class="theme-field px-3 py-2.5" required placeholder="Deep Tissue Massage">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Category</label>
                    <input name="category" list="serviceCategoryOptions" class="theme-field px-3 py-2.5" required placeholder="Choose or enter a category" autocomplete="off">
                    <p class="mt-1.5 text-xs text-[var(--muted)]">Choose an existing category or type a new one.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Price</label>
                        <input name="price" type="number" step="0.01" class="theme-field px-3 py-2.5" required placeholder="0.00">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Duration <span class="font-normal">(optional)</span></label>
                        <input name="duration" type="number" min="1" class="theme-field px-3 py-2.5" placeholder="No fixed duration">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Number of sessions</label>
                    <input name="session_count" type="number" min="1" max="100" value="1" class="theme-field px-3 py-2.5" required>
                    <p class="mt-1.5 text-xs text-[var(--muted)]">Use 1 for a regular service or 5/10 for a package.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Description</label>
                    <textarea name="description" rows="3" class="theme-field resize-none px-3 py-2.5" placeholder="Optional"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-[var(--ink)]">
                    <input type="checkbox" name="requires_consent" value="1" class="h-4 w-4 rounded accent-[var(--desert-rock)]">
                    Require client consent and signature
                </label>

                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeEditModal()" aria-label="Close edit modal"></button>

        <div class="theme-card relative w-full max-w-md overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <h2 class="text-lg font-semibold text-[var(--ink)]">Edit Service</h2>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeEditModal()">Close</button>
            </div>

            <form id="editForm" method="POST" class="space-y-4 px-5 py-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Service name</label>
                    <input id="edit_name" name="name" class="theme-field px-3 py-2.5" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Category</label>
                    <input id="edit_category" name="category" list="serviceCategoryOptions" class="theme-field px-3 py-2.5" required autocomplete="off">
                    <p class="mt-1.5 text-xs text-[var(--muted)]">Choose an existing category or type a new one.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Price</label>
                        <input id="edit_price" name="price" type="number" step="0.01" class="theme-field px-3 py-2.5" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Duration <span class="font-normal">(optional)</span></label>
                        <input id="edit_duration" name="duration" type="number" min="1" class="theme-field px-3 py-2.5" placeholder="No fixed duration">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Number of sessions</label>
                    <input id="edit_session_count" name="session_count" type="number" min="1" max="100" class="theme-field px-3 py-2.5" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Description</label>
                    <textarea id="edit_description" name="description" rows="3" class="theme-field resize-none px-3 py-2.5"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-[var(--ink)]">
                    <input id="edit_requires_consent" type="checkbox" name="requires_consent" value="1" class="h-4 w-4 rounded accent-[var(--desert-rock)]">
                    Require client consent and signature
                </label>

                <label class="flex items-center gap-2 text-sm font-semibold text-[var(--ink)]">
                    <input id="edit_is_active" type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded accent-[var(--desert-rock)]">
                    Available for new bookings
                </label>

                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeDeleteModal()" aria-label="Close delete modal"></button>

        <div class="theme-card relative w-full max-w-sm rounded-2xl p-5">
            <h2 class="text-lg font-semibold text-[var(--ink)]">Delete Service?</h2>
            <p class="mt-1 text-sm text-[var(--muted)]">The service will be removed from the catalog. Existing appointment history will be preserved.</p>

            <div class="mt-5 grid gap-2 sm:grid-cols-2">
                <button type="button" class="muted-button h-11 w-full rounded-xl px-4 text-sm font-semibold" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="h-11 w-full rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-700">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function lockBody(value) { document.body.style.overflow = value ? 'hidden' : ''; }
function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); lockBody(true); }
function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); lockBody(false); }

function openEditModal(service) {
    document.getElementById('editModal').classList.remove('hidden');
    lockBody(true);
    document.getElementById('editForm').action = `/services/${service.id}`;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_category').value = service.category;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_duration').value = service.duration ?? '';
    document.getElementById('edit_session_count').value = service.session_count ?? 1;
    document.getElementById('edit_description').value = service.description ?? '';
    document.getElementById('edit_requires_consent').checked = Boolean(service.requires_consent);
    document.getElementById('edit_is_active').checked = Boolean(service.is_active);
}

function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); lockBody(false); }

function openDeleteModal(id) {
    document.getElementById('deleteModal').classList.remove('hidden');
    lockBody(true);
    document.getElementById('deleteForm').action = `/services/${id}`;
}

function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden'); lockBody(false); }
</script>

@endsection
