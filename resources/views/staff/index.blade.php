@extends('layouts.app')
@extends('layouts.dashboard')
@section('header', 'User Management')
@section('subheader', 'Manage staff accounts, roles, and permissions')

@section('content')

@php
    $canManage = in_array(auth()->user()->role, ['admin','management']);
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
            <input type="text" id="searchInput" placeholder="Search name or email…"
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
        </div>

        <select id="roleFilter"
                class="w-full sm:w-44 px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
            <option value="">All roles</option>
            <option value="admin">Admin</option>
            <option value="management">Management</option>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
        </select>

    </div>

    @if($canManage)
    <button onclick="openModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add User
    </button>
    @endif

</div>

{{-- ===================== DESKTOP TABLE ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm" id="desktopTable">

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                <th class="px-5 py-3.5 text-left">User</th>
                <th class="px-5 py-3.5 text-left">Email</th>
                <th class="px-5 py-3.5 text-left">Role</th>
                <th class="px-5 py-3.5 text-left">Created</th>
                @if($canManage)
                <th class="px-5 py-3.5 text-left">Actions</th>
                @endif
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-50">
        @forelse($users as $u)

        <tr class="user-row hover:bg-[#faf7f2] transition-colors"
            data-name="{{ strtolower($u->name) }}"
            data-email="{{ strtolower($u->email) }}"
            data-role="{{ $u->role }}">

            <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#c8a96a]/20 text-[#8a6a30] flex items-center justify-center text-xs font-semibold shrink-0">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <p class="font-medium text-gray-900">{{ $u->name }}</p>
                </div>
            </td>

            <td class="px-5 py-4 text-gray-500 text-xs">{{ $u->email }}</td>

            <td class="px-5 py-4">
                @if($canManage)
                <select class="role-select px-3 py-1.5 rounded-lg text-xs border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition"
                        data-id="{{ $u->id }}">
                    <option value="admin"      @selected($u->role=='admin')>Admin</option>
                    <option value="management" @selected($u->role=='management')>Management</option>
                    <option value="staff"      @selected($u->role=='staff')>Staff</option>
                    <option value="customer"   @selected($u->role=='customer')>Customer</option>
                </select>
                @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    @if($u->role=='admin')      bg-[#c8a96a]/10 text-[#8a6a30]  ring-1 ring-[#c8a96a]/30
                    @elseif($u->role=='management') bg-[#3a2a22]/10 text-[#3a2a22] ring-1 ring-[#3a2a22]/20
                    @elseif($u->role=='staff')  bg-blue-50 text-blue-700 ring-1 ring-blue-200
                    @else                       bg-gray-100 text-gray-600 ring-1 ring-gray-200
                    @endif">
                    {{ ucfirst($u->role) }}
                </span>
                @endif
            </td>

            <td class="px-5 py-4 text-xs text-gray-400">
                {{ $u->created_at->format('M d, Y') }}
            </td>

            @if($canManage)
            <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                    <button onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ $u->role }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                    <button onclick="openDeleteModal({{ $u->id }}, '{{ addslashes($u->name) }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </div>
            </td>
            @endif

        </tr>

        @empty
        <tr>
            <td colspan="5" class="px-5 py-16 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-400 text-sm">No users found.</p>
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>

</div>

{{-- ===================== MOBILE CARDS ===================== --}}
<div class="grid gap-3" id="mobileCards">

@forelse($users as $u)

<div class="user-row bg-white border border-gray-100 rounded-2xl p-4 shadow-sm"
     data-name="{{ strtolower($u->name) }}"
     data-email="{{ strtolower($u->email) }}"
     data-role="{{ $u->role }}">

    <div class="flex justify-between items-start gap-2 mb-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-[#c8a96a]/20 text-[#8a6a30] flex items-center justify-center text-sm font-semibold shrink-0">
                {{ strtoupper(substr($u->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">{{ $u->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $u->email }}</p>
            </div>
        </div>

        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shrink-0
            @if($u->role=='admin')      bg-[#c8a96a]/10 text-[#8a6a30]  ring-1 ring-[#c8a96a]/30
            @elseif($u->role=='management') bg-[#3a2a22]/10 text-[#3a2a22] ring-1 ring-[#3a2a22]/20
            @elseif($u->role=='staff')  bg-blue-50 text-blue-700 ring-1 ring-blue-200
            @else                       bg-gray-100 text-gray-600 ring-1 ring-gray-200
            @endif">
            {{ ucfirst($u->role) }}
        </span>
    </div>

    <p class="text-xs text-gray-400 mb-3">Joined {{ $u->created_at->format('M d, Y') }}</p>

    @if($canManage)
    <div class="border-t border-gray-100 pt-3 flex flex-wrap gap-2">
        <select class="role-select flex-1 min-w-[140px] px-3 py-2 rounded-xl text-xs border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50"
                data-id="{{ $u->id }}">
            <option value="admin"      @selected($u->role=='admin')>Admin</option>
            <option value="management" @selected($u->role=='management')>Management</option>
            <option value="staff"      @selected($u->role=='staff')>Staff</option>
            <option value="customer"   @selected($u->role=='customer')>Customer</option>
        </select>

        <button onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ $u->role }}')"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </button>

        <button onclick="openDeleteModal({{ $u->id }}, '{{ addslashes($u->name) }}')"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete
        </button>
    </div>
    @endif

</div>

@empty
<div class="text-center py-16 text-gray-400 text-sm">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    No users found.
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

{{-- ===================== PAGINATION ===================== --}}
<div class="mt-6 flex justify-center">
    {{ $users->links() }}
</div>

{{-- ===================== ADD USER MODAL ===================== --}}
@if($canManage)
<div id="userModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Add User</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('staff.store') }}" class="px-6 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Full name</label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="email" required placeholder="email@example.com"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Role</label>
                    <select name="role"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                        <option value="staff">Staff</option>
                        <option value="management">Management</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Create User
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ===================== EDIT USER MODAL ===================== --}}
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Edit User</h2>
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
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Full name</label>
                    <input id="edit_name" type="text" name="name" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                    <input id="edit_email" type="email" name="email" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">New password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="••••••••"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Role</label>
                    <select id="edit_role" name="role"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                        <option value="admin">Admin</option>
                        <option value="management">Management</option>
                        <option value="staff">Staff</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeEditModal()"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a96a] hover:bg-[#b8955a] text-white text-sm font-medium transition">
                        Save Changes
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ===================== DELETE USER MODAL ===================== --}}
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
                        <h2 class="text-base font-semibold text-gray-900">Delete User?</h2>
                        <p id="deleteUserName" class="text-xs text-gray-400 mt-0.5"></p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-5">This action cannot be undone. All data associated with this user will be permanently removed.</p>
                <div class="flex gap-2">
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
@endif

{{-- ===================== SCRIPTS ===================== --}}
<script>

// SEARCH + FILTER
const searchInput = document.getElementById('searchInput');
const roleFilter  = document.getElementById('roleFilter');

function filterUsers() {
    const search = searchInput.value.toLowerCase();
    const role   = roleFilter.value;
    document.querySelectorAll('.user-row').forEach(row => {
        const matchSearch = row.dataset.name.includes(search) || row.dataset.email.includes(search);
        const matchRole   = role === '' || role === row.dataset.role;
        row.style.display = (matchSearch && matchRole) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterUsers);
roleFilter.addEventListener('change', filterUsers);

// ROLE UPDATE (inline select)
document.querySelectorAll('.role-select').forEach(select => {
    select.addEventListener('change', async function () {
        await fetch(`/staff/${this.dataset.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ role: this.value })
        });
    });
});

// ADD USER MODAL
function openModal() {
    document.getElementById('userModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// EDIT USER MODAL
function openEditModal(id, name, email, role) {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('editForm').action = `/staff/${id}`;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value  = role;
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// DELETE USER MODAL
function openDeleteModal(id, name) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('deleteForm').action  = `/staff/${id}`;
    document.getElementById('deleteUserName').innerText = name;
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

</script>

@endsection