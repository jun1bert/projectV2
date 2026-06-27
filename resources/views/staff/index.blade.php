@extends('layouts.dashboard')

@section('header', 'User Management')
@section('subheader', 'Manage team accounts, reception access, and permissions')
<title><?= config('app.name') ?> | User Management</title>
@section('content')
@php
    $canManage = in_array(auth()->user()->role, ['admin', 'management'], true);
    $roles = [
        'admin' => 'Admin',
        'management' => 'Management',
        'reception' => 'Reception',
        'staff' => 'Staff',
        'customer' => 'Customer',
    ];

    $roleBadge = [
        'admin' => 'bg-[var(--desert-rock)] text-[var(--feather-white)]',
        'management' => 'bg-[var(--creamed-oat)] text-[var(--ink)] ring-1 ring-[var(--desert-rock)]/20',
        'reception' => 'bg-[#f7efe7] text-[var(--desert-rock)] ring-1 ring-[var(--desert-rock)]/25',
        'staff' => 'bg-[var(--porcelain-mist)] text-[var(--muted)] ring-1 ring-[var(--soft-sandstone)]/50',
        'customer' => 'bg-[var(--feather-white)] text-[var(--muted)] ring-1 ring-[var(--soft-sandstone)]/45',
    ];
@endphp

<div class="space-y-6">
    <div class="theme-panel rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_12rem]">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[var(--muted)]">
                        Search
                    </span>
                    <input type="text" id="searchInput" placeholder="Name or email"
                           class="theme-field w-full rounded-xl py-3 pl-20 pr-4 text-sm">
                </label>

                <select id="roleFilter" class="theme-field w-full rounded-xl px-4 py-3 text-sm">
                    <option value="">All roles</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($canManage)
                <button onclick="openModal()"
                        class="rounded-xl bg-[var(--desert-rock)] px-5 py-3 text-sm font-bold text-[var(--feather-white)]
                               transition hover:bg-[#8f7663] active:scale-[.98]">
                    Add User
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="desktopTable" class="theme-card hidden overflow-hidden rounded-2xl md:block">
        <table class="theme-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    @if($canManage)
                        <th class="text-center">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--soft-sandstone)]/30">
                @forelse($users as $u)
                    <tr class="user-row transition hover:bg-[var(--porcelain-mist)]/70"
                        data-name="{{ strtolower($u->name) }}"
                        data-email="{{ strtolower($u->email) }}"
                        data-role="{{ $u->role }}">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[var(--creamed-oat)] text-sm font-bold text-[var(--desert-rock)]">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="font-bold text-[var(--ink)]">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-xs text-[var(--muted)]">{{ $u->email }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $roleBadge[$u->role] ?? $roleBadge['customer'] }}">
                                {{ $roles[$u->role] ?? ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-[var(--muted)]">{{ $u->created_at->format('M d, Y') }}</td>
                        @if($canManage)
                            <td class="px-5 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="openEditModal({{ $u->id }}, @js($u->name), @js($u->email), @js($u->role))"
                                            class="rounded-lg bg-[var(--creamed-oat)] px-3 py-2 text-xs font-bold text-[var(--ink)] transition hover:bg-[var(--soft-sandstone)]/70">
                                        Edit
                                    </button>
                                    <button onclick="openDeleteModal({{ $u->id }}, @js($u->name))"
                                            class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManage ? 5 : 4 }}" class="px-5 py-16 text-center text-sm text-[var(--muted)]">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="mobileCards" class="grid gap-3 md:hidden">
        @forelse($users as $u)
            <article class="user-row theme-card rounded-2xl p-4"
                     data-name="{{ strtolower($u->name) }}"
                     data-email="{{ strtolower($u->email) }}"
                     data-role="{{ $u->role }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[var(--creamed-oat)] text-sm font-bold text-[var(--desert-rock)]">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-bold text-[var(--ink)]">{{ $u->name }}</h3>
                            <p class="truncate text-xs text-[var(--muted)]">{{ $u->email }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $roleBadge[$u->role] ?? $roleBadge['customer'] }}">
                        {{ $roles[$u->role] ?? ucfirst($u->role) }}
                    </span>
                </div>

                <p class="mt-3 text-xs text-[var(--muted)]">Joined {{ $u->created_at->format('M d, Y') }}</p>

                @if($canManage)
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-[var(--soft-sandstone)]/35 pt-4">
                        <button onclick="openEditModal({{ $u->id }}, @js($u->name), @js($u->email), @js($u->role))"
                                class="flex-1 rounded-xl bg-[var(--creamed-oat)] px-4 py-2.5 text-xs font-bold text-[var(--ink)] transition hover:bg-[var(--soft-sandstone)]/70">
                            Edit
                        </button>
                        <button onclick="openDeleteModal({{ $u->id }}, @js($u->name))"
                                class="flex-1 rounded-xl bg-red-50 px-4 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-100">
                            Delete
                        </button>
                    </div>
                @endif
            </article>
        @empty
            <div class="theme-card rounded-2xl p-10 text-center text-sm text-[var(--muted)]">
                No users found.
            </div>
        @endforelse
    </div>

    <div class="flex justify-center">
        {{ $users->links() }}
    </div>
</div>

@if($canManage)
    <div id="userModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-[#2c241f]/55 backdrop-blur-sm" onclick="closeModal()"></div>
            <div class="theme-card modal-panel relative w-full max-w-lg overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between border-b border-[var(--soft-sandstone)]/35 px-6 py-4">
                    <h2 class="text-base font-bold text-[var(--ink)]">Add User</h2>
                    <button onclick="closeModal()" class="text-sm font-bold text-[var(--muted)] hover:text-[var(--ink)]">Close</button>
                </div>

                <form method="POST" action="{{ route('staff.store') }}" class="space-y-4 px-6 py-5">
                    @csrf
                    @include('staff.partials.user-fields', ['roles' => $roles])

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" onclick="closeModal()" class="rounded-xl border border-[var(--soft-sandstone)]/60 px-4 py-2.5 text-sm font-bold text-[var(--muted)] hover:bg-[var(--porcelain-mist)]">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-[var(--desert-rock)] px-4 py-2.5 text-sm font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-[#2c241f]/55 backdrop-blur-sm" onclick="closeEditModal()"></div>
            <div class="theme-card modal-panel relative w-full max-w-lg overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between border-b border-[var(--soft-sandstone)]/35 px-6 py-4">
                    <h2 class="text-base font-bold text-[var(--ink)]">Edit User</h2>
                    <button onclick="closeEditModal()" class="text-sm font-bold text-[var(--muted)] hover:text-[var(--ink)]">Close</button>
                </div>

                <form id="editForm" method="POST" class="space-y-4 px-6 py-5">
                    @csrf
                    @method('PUT')
                    @include('staff.partials.user-fields', ['roles' => $roles, 'edit' => true])

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" onclick="closeEditModal()" class="rounded-xl border border-[var(--soft-sandstone)]/60 px-4 py-2.5 text-sm font-bold text-[var(--muted)] hover:bg-[var(--porcelain-mist)]">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-[var(--desert-rock)] px-4 py-2.5 text-sm font-bold text-[var(--feather-white)] hover:bg-[#8f7663]">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-[#2c241f]/55 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
            <div class="theme-card modal-panel relative w-full max-w-sm rounded-2xl p-6">
                <h2 class="text-lg font-bold text-[var(--ink)]">Delete User?</h2>
                <p id="deleteUserName" class="mt-1 text-sm text-[var(--muted)]"></p>
                <p class="mt-4 text-sm text-[var(--muted)]">This action cannot be undone.</p>

                <div class="mt-6 grid grid-cols-2 gap-2">
                    <button onclick="closeDeleteModal()" class="rounded-xl border border-[var(--soft-sandstone)]/60 px-4 py-2.5 text-sm font-bold text-[var(--muted)] hover:bg-[var(--porcelain-mist)]">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    .theme-field {
        border: 1px solid rgba(164, 141, 120, .28);
        background: rgba(250, 249, 246, .86);
        color: var(--ink);
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .theme-field:focus {
        border-color: var(--desert-rock);
        box-shadow: 0 0 0 3px rgba(164, 141, 120, .18);
    }
</style>

<script>
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');

function filterUsers() {
    const search = searchInput.value.toLowerCase();
    const role = roleFilter.value;

    document.querySelectorAll('.user-row').forEach(row => {
        const matchesSearch = row.dataset.name.includes(search) || row.dataset.email.includes(search);
        const matchesRole = role === '' || role === row.dataset.role;
        row.style.display = matchesSearch && matchesRole ? '' : 'none';
    });
}

searchInput?.addEventListener('input', filterUsers);
roleFilter?.addEventListener('change', filterUsers);

function openModal() {
    document.getElementById('userModal')?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('userModal')?.classList.add('hidden');
    document.body.style.overflow = '';
}

function openEditModal(id, name, email, role) {
    const form = document.getElementById('editForm');
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    form.action = `/staff/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal')?.classList.add('hidden');
    document.body.style.overflow = '';
}

function openDeleteModal(id, name) {
    document.getElementById('deleteForm').action = `/staff/${id}`;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal')?.classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
@endsection
