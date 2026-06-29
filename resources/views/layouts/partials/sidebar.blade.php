@php
    $user = auth()->user();
    $role = $user?->role;

    $links = [
        [
            'label' => 'Overview',
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'roles' => ['admin', 'staff', 'reception', 'management'],
        ],
        [
            'label' => 'My Bookings',
            'href' => route('customer.bookings.index'),
            'active' => request()->routeIs('customer.bookings.*'),
            'roles' => ['customer'],
        ],
        [
            'label' => 'Appointments',
            'href' => route('appointments.index'),
            'active' => request()->routeIs('appointments.*'),
            'roles' => ['admin', 'staff', 'reception', 'management'],
        ],
        [
            'label' => 'Services',
            'href' => route('services.index'),
            'active' => request()->routeIs('services.*'),
            'roles' => ['admin', 'management'],
        ],
        [
            'label' => 'Staff',
            'href' => route('staff.index'),
            'active' => request()->routeIs('staff.*'),
            'roles' => ['admin', 'management'],
        ],
        [
            'label' => 'Reports',
            'href' => route('reports.index'),
            'active' => request()->routeIs('reports.index'),
            'roles' => ['admin', 'management'],
        ],
        [
            'label' => 'Customer Services',
            'href' => route('reports.customer-services'),
            'active' => request()->routeIs('reports.customer-services'),
            'roles' => ['admin', 'management'],
        ],
        [
            'label' => 'Gallery',
            'href' => route('gallery.index'),
            'active' => request()->routeIs('gallery.*'),
            'roles' => ['admin', 'management', 'reception'],
        ],
    ];
@endphp

<aside id="sidebar"
    class="sidebar fixed left-0 top-0 z-50 flex h-full w-64 -translate-x-full flex-col
           border-r border-[var(--desert-rock)]/25 p-5 shadow-2xl
           transition-transform duration-300 ease-in-out md:translate-x-0">

    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-2xl border border-[var(--desert-rock)]/15 bg-[var(--feather-white)]/55 px-3 py-3 shadow-sm">
        <img src="{{ asset('images/martinis-logo.png') }}"
             alt="Martinis and Manicures"
             class="h-12 w-auto max-w-[9.5rem] object-contain">
    </a>

    <p class="mt-4 px-2 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--muted)]">
        {{ $role === 'customer' ? 'Client Portal' : 'Spa Management' }}
    </p>

    <nav class="mt-8 space-y-2 text-sm font-semibold">
        @foreach($links as $link)
            @if($link['roles'] === null || in_array($role, $link['roles'], true))
                <a href="{{ $link['href'] }}"
                   class="block rounded-xl border px-4 py-3 transition
                          {{ $link['active']
                              ? 'border-[var(--desert-rock)] bg-[var(--desert-rock)] text-[var(--feather-white)] shadow-lg shadow-[#a48d78]/25'
                              : 'border-transparent text-[var(--ink)] hover:border-[var(--desert-rock)]/20 hover:bg-[var(--feather-white)]/65 hover:text-[var(--desert-rock)]' }}">
                    {{ $link['label'] }}
                </a>
            @endif
        @endforeach
    </nav>

    <div class="mt-auto rounded-2xl border border-[var(--desert-rock)]/18 bg-[var(--feather-white)]/70 p-4 text-xs text-[var(--muted)] shadow-sm">
        <span class="block">Logged in as</span>
        <span class="mt-1 block text-sm font-bold capitalize text-[var(--ink)]">
            {{ $role }}
        </span>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit"
            class="w-full rounded-xl border border-red-200/80 bg-red-50 px-4 py-3 text-left
                   text-sm font-bold text-red-700 transition hover:bg-red-100">
            Logout
        </button>
    </form>
</aside>
