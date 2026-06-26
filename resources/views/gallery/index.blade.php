@extends('layouts.dashboard')

@section('header', 'Gallery Management')
@section('subheader', 'Manage images shown on the welcome page')

@section('content')
<title><?= config('app.name') ?> | Gallery Management</title>
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

@if(session('success'))
<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-xl font-semibold text-[var(--ink)]">Gallery Library</h3>
        <p class="mt-1 text-sm text-[var(--muted)]">{{ $images->count() }} image{{ $images->count() !== 1 ? 's' : '' }} uploaded</p>
    </div>

    <button type="button" onclick="openCreateModal()"
            class="theme-button inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
        Upload Images
    </button>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse($images as $image)
    <article class="theme-card overflow-hidden rounded-2xl">
        <div class="relative">
            <img src="{{ Storage::url($image->path) }}"
                 alt="{{ $image->title ?? 'Gallery image' }}"
                 class="aspect-square w-full object-cover">

            <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold ring-1
                         {{ $image->is_published ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-stone-900/70 text-white ring-white/20' }}">
                {{ $image->is_published ? 'Visible' : 'Hidden' }}
            </span>
        </div>

        <div class="p-4">
            <h3 class="truncate text-sm font-semibold text-[var(--ink)]">{{ $image->title ?? 'Untitled Image' }}</h3>
            <p class="mt-1 line-clamp-2 min-h-[36px] text-sm text-[var(--muted)]">{{ $image->caption ?? 'No caption provided.' }}</p>

            <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-[rgba(164,141,120,.16)] pt-4">
                <form action="{{ route('gallery.togglePublish', $image) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="muted-button rounded-xl px-3 py-2 text-xs font-semibold transition">
                        {{ $image->is_published ? 'Hide' : 'Publish' }}
                    </button>
                </form>

                <button type="button" onclick="openDeleteModal({{ $image->id }})"
                        class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 ring-1 ring-rose-100 transition hover:bg-rose-100">
                    Delete
                </button>
            </div>
        </div>
    </article>
    @empty
    <div class="theme-card col-span-full rounded-2xl px-5 py-16 text-center text-sm text-[var(--muted)]">
        No images uploaded yet.
    </div>
    @endforelse
</div>

<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeCreateModal()" aria-label="Close upload modal"></button>

        <div class="theme-card relative w-full max-w-lg overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--ink)]">Upload Images</h2>
                    <p class="text-xs text-[var(--muted)]">Add one or more images to the public gallery.</p>
                </div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeCreateModal()">Close</button>
            </div>

            <form action="{{ route('gallery.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 px-5 py-5">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="theme-field px-3 py-2.5" placeholder="Optional">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Caption</label>
                    <input type="text" name="caption" value="{{ old('caption') }}" class="theme-field px-3 py-2.5" placeholder="Optional">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Images</label>
                    <input id="imagesInput"
                           type="file"
                           name="images[]"
                           multiple
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           onchange="previewImages(event)"
                           class="theme-field px-3 py-2.5 text-[var(--muted)]
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-[rgba(164,141,120,.14)]
                                  file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[var(--desert-rock)]">

                    <div id="imagePreview" class="mt-3 grid grid-cols-3 gap-3"></div>
                    <p class="mt-2 text-xs text-[var(--muted)]">JPEG, PNG, WebP or GIF. Max 5 MB each. Up to 20 files.</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-[var(--ink)]">
                    <input type="checkbox" name="is_published" value="1" checked class="h-4 w-4 rounded accent-[var(--desert-rock)]">
                    Publish immediately
                </label>

                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeCreateModal()">Cancel</button>
                    <button type="submit" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeDeleteModal()" aria-label="Close delete modal"></button>

        <div class="theme-card relative w-full max-w-sm rounded-2xl p-5">
            <h2 class="text-lg font-semibold text-[var(--ink)]">Delete Image?</h2>
            <p class="mt-1 text-sm text-[var(--muted)]">This action cannot be undone.</p>

            <div class="mt-5 grid gap-2 sm:grid-cols-2">
                <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function lockBody(value) { document.body.style.overflow = value ? 'hidden' : ''; }
function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); lockBody(true); }
function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); lockBody(false); }

function openDeleteModal(id) {
    document.getElementById('deleteModal').classList.remove('hidden');
    lockBody(true);
    document.getElementById('deleteForm').action = `/gallery/${id}`;
}

function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden'); lockBody(false); }

function previewImages(event) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    Array.from(event.target.files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (readerEvent) => {
            const img = document.createElement('img');
            img.src = readerEvent.target.result;
            img.className = 'aspect-square w-full rounded-xl border border-[rgba(164,141,120,.18)] object-cover';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>

@endsection
