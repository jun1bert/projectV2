{{-- resources/views/gallery/index.blade.php --}}
@extends('layouts.dashboard')

@section('header', 'Gallery Management')
@section('subheader', 'Manage images shown on the welcome page')

@section('content')

{{-- ===================== FLASH MESSAGES ===================== --}}
@if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

{{-- ===================== TOOLBAR ===================== --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <p class="text-sm text-gray-500">
        {{ $images->count() }} image{{ $images->count() !== 1 ? 's' : '' }} uploaded
    </p>

    <button onclick="openCreateModal()"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Upload Images
    </button>
</div>

{{-- ===================== IMAGE GRID ===================== --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

@forelse($images as $image)

<div class="group bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
    <div class="relative">
        <img src="{{ Storage::url($image->path) }}"
             alt="{{ $image->title ?? 'Gallery image' }}"
             class="w-full aspect-square object-cover">

        @unless($image->is_published)
            <span class="absolute top-2 left-2 px-2 py-1 rounded-lg bg-gray-900/70 text-white text-[10px] font-medium">
                Hidden
            </span>
        @endunless
    </div>

    <div class="p-4">
        <h3 class="font-semibold text-gray-900 text-sm truncate">
            {{ $image->title ?? 'Untitled Image' }}
        </h3>

        <p class="text-xs text-gray-400 mt-1 line-clamp-2 min-h-[32px]">
            {{ $image->caption ?? 'No caption provided.' }}
        </p>

        <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-50">
            <form action="{{ route('gallery.togglePublish', $image) }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition
                    {{ $image->is_published
                        ? 'bg-green-50 hover:bg-green-100 text-green-600'
                        : 'bg-gray-100 hover:bg-gray-200 text-gray-500' }}">
                    {{ $image->is_published ? 'Visible' : 'Hidden' }}
                </button>
            </form>

            <button onclick="openDeleteModal({{ $image->id }})"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </div>
    </div>
</div>

@empty

<div class="col-span-full text-center py-16">
    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16l4-4a3 3 0 014.243 0L21 22M14 14l2-2a3 3 0 014.243 0L21 13M5 8h.01M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/>
    </svg>
    <p class="text-gray-400 text-sm">No images uploaded yet.</p>
</div>

@endforelse

</div>

{{-- ===================== CREATE MODAL ===================== --}}
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeCreateModal()"></div>

        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Upload Images</h2>

                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('gallery.store') }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Title <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="e.g. Nail Art Collection"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Caption <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" name="caption" value="{{ old('caption') }}"
                           placeholder="Short description shown in gallery"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Images <span class="text-red-500">*</span>
                    </label>
                    <input id="imagesInput" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif"
       onchange="previewImages(event)"
       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500
              file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
              file:bg-[#c8a96a]/10 file:text-[#c8a96a] file:text-xs file:font-medium
              focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">

<div id="imagePreview" class="grid grid-cols-3 gap-3 mt-3"></div>
                    <p class="text-xs text-gray-400 mt-1.5">
                        JPEG, PNG, WebP or GIF · Max 5 MB each · Up to 20 files
                    </p>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 rounded accent-[#c8a96a]">
                    Publish immediately
                </label>

                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeCreateModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>

                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== DELETE MODAL ===================== --}}
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>

        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 01116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Delete Image?</h2>
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
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function openDeleteModal(id) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('deleteForm').action = `/gallery/${id}`;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
<script>
function previewImages(event) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    Array.from(event.target.files).forEach(file => {
        const reader = new FileReader();

        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full aspect-square object-cover rounded-xl border border-gray-100';
            preview.appendChild(img);
        };

        reader.readAsDataURL(file);
    });
}
</script>
@endsection