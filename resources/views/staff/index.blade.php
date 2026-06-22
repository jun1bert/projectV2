@extends('layouts.app')
@extends('layouts.dashboard')
@section('header', 'User Management')
@section('subheader', 'Manage staff accounts, roles, and permissions')

@section('content')

<!-- ===================== TOP TOOLBAR ===================== -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">

    <div class="flex gap-3 w-full md:w-auto">

        <input type="text"
               id="searchInput"
               placeholder="Search name or email..."
               class="w-full md:w-80 px-4 py-2 rounded-xl border bg-white shadow-sm text-sm">

        <select id="roleFilter"
                class="w-full md:w-48 px-4 py-2 rounded-xl border bg-white text-sm">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="management">Management</option>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
        </select>

    </div>

    <button onclick="openModal()"
            class="px-4 py-2 bg-black text-white rounded-xl text-sm hover:bg-gray-800 transition">
        + Add User
    </button>

</div>

<!-- ===================== DESKTOP TABLE ===================== -->
<div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">

    <table class="w-full text-sm">

        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
                <th class="p-4 text-left">User</th>
                <th class="p-4 text-left">Email</th>
                <th class="p-4 text-left">Role</th>
                <th class="p-4 text-left">Created</th>
                <th class="p-4 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>

        @forelse($users as $u)

        <tr class="border-b hover:bg-gray-50 user-row"
            data-name="{{ strtolower($u->name) }}"
            data-email="{{ strtolower($u->email) }}"
            data-role="{{ $u->role }}">

            <!-- USER -->
            <td class="p-4 font-medium">{{ $u->name }}</td>

            <!-- EMAIL -->
            <td class="p-4 text-gray-600">{{ $u->email }}</td>

            <!-- ROLE -->
            <td class="p-4">
                <select class="role-select text-xs px-2 py-1 rounded border bg-white"
                        data-id="{{ $u->id }}">
                    <option value="admin" @selected($u->role=='admin')>Admin</option>
                    <option value="management" @selected($u->role=='management')>Management</option>
                    <option value="staff" @selected($u->role=='staff')>Staff</option>
                    <option value="customer" @selected($u->role=='customer')>Customer</option>
                </select>
            </td>

            <!-- DATE -->
            <td class="p-4 text-xs text-gray-500">
                {{ $u->created_at->format('M d, Y') }}
            </td>

            <!-- ACTION -->
            <td class="p-4">
                <button class="px-3 py-1 text-xs rounded-lg bg-red-50 text-red-600 hover:bg-red-100">
                    Delete
                </button>
            </td>

        </tr>

        @empty
        <tr>
            <td colspan="5" class="p-4 text-center text-gray-500">
                No users found
            </td>
        </tr>
        @endforelse

        </tbody>
    </table>

</div>

<!-- ===================== MOBILE ===================== -->
<div class="grid gap-4 md:hidden">

@forelse($users as $u)

<div class="bg-white rounded-2xl shadow p-4 user-row"
     data-name="{{ strtolower($u->name) }}"
     data-email="{{ strtolower($u->email) }}"
     data-role="{{ $u->role }}">

    <div class="flex justify-between">

        <div>
            <p class="font-semibold">{{ $u->name }}</p>
            <p class="text-xs text-gray-500">{{ $u->email }}</p>
        </div>

        <span class="text-xs px-3 py-1 rounded bg-gray-100">
            {{ ucfirst($u->role) }}
        </span>

    </div>

    <div class="mt-3">
        <select class="role-select w-full text-sm px-3 py-2 border rounded"
                data-id="{{ $u->id }}">
            <option value="admin" @selected($u->role=='admin')>Admin</option>
            <option value="management" @selected($u->role=='management')>Management</option>
            <option value="staff" @selected($u->role=='staff')>Staff</option>
            <option value="customer" @selected($u->role=='customer')>Customer</option>
        </select>
    </div>

</div>

@empty
<div class="text-center text-gray-500">No users found</div>
@endforelse

</div>

<!-- ===================== PAGINATION ===================== -->
<div class="mt-6 flex justify-center">
    {{ $users->links() }}
</div>

<!-- ===================== ADD USER MODAL ===================== -->
<div id="userModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-xl">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Add User</h2>
            <button onclick="closeModal()" class="text-gray-500">✕</button>
        </div>

        <form method="POST" action="{{ route('staff.store') }}" class="space-y-3">
            @csrf

            <input type="text" name="name" placeholder="Name"
                   class="w-full px-4 py-2 border rounded-xl">

            <input type="email" name="email" placeholder="Email"
                   class="w-full px-4 py-2 border rounded-xl">

            <input type="password" name="password" placeholder="Password"
                   class="w-full px-4 py-2 border rounded-xl">

            <select name="role"
                    class="w-full px-4 py-2 border rounded-xl">
                <option value="staff">Staff</option>
                <option value="management">Management</option>
                <option value="customer">Customer</option>
            </select>

            <button type="submit"
                    class="w-full bg-black text-white py-2 rounded-xl hover:bg-gray-800">
                Create User
            </button>

        </form>

    </div>
</div>

<!-- ===================== SCRIPTS ===================== -->
<script>

// SEARCH + FILTER
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');

function filterUsers() {
    const search = searchInput.value.toLowerCase();
    const role = roleFilter.value;

    document.querySelectorAll('.user-row').forEach(row => {

        const name = row.dataset.name;
        const email = row.dataset.email;
        const userRole = row.dataset.role;

        const matchSearch = name.includes(search) || email.includes(search);
        const matchRole = role === "" || role === userRole;

        row.style.display = (matchSearch && matchRole) ? "" : "none";
    });
}

searchInput.addEventListener('input', filterUsers);
roleFilter.addEventListener('change', filterUsers);

// ROLE UPDATE
document.querySelectorAll('.role-select').forEach(select => {
    select.addEventListener('change', async function () {

        const res = await fetch(`/staff/${this.dataset.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ role: this.value })
        });

    });
});

// MODAL
function openModal() {
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
    document.getElementById('userModal').classList.remove('flex');
}

</script>

@endsection