<aside id="sidebar"
    class="sidebar w-64 text-white flex flex-col p-6
           fixed top-0 left-0 h-full z-50
           transform -translate-x-full
           md:translate-x-0
           transition-transform duration-300 ease-in-out">

    <h1 class="text-2xl font-bold gold">Martinis & Manicures</h1>
    <p class="text-xs text-white/60 mt-1">Luxury Spa System</p>

    <nav class="space-y-4 text-sm mt-10">

        <!-- DASHBOARD (ALL ROLES) -->
        <a href="/dashboard" class="block hover:text-yellow-300">
            📊 Overview
        </a>

        <!-- APPOINTMENTS (ALL ROLES - BUT ROLE-BASED FEATURES INSIDE PAGE) -->
        @if(in_array(auth()->user()->role, ['admin','staff','management']))
            <a href="/appointments" class="block hover:text-yellow-300">
                📅 Appointments
            </a>
        @endif

        <!-- ADMIN + MANAGEMENT ONLY -->
        @if(in_array(auth()->user()->role, ['admin','management']))

            <a href="/services" class="block hover:text-yellow-300">
                💅 Services
            </a>

            <a href="/staff" class="block hover:text-yellow-300">
                👩‍💼 Staff
            </a>

            <a href="/commissions" class="block hover:text-yellow-300">
                📊 Commission
            </a>

            <a href="/reports" class="block hover:text-yellow-300">
                📈 Reports
            </a>

            <a href="/inventory" class="block hover:text-yellow-300">
                📦 Inventory
            </a>

        @endif

        <!-- GALLERY (ADMIN + MANAGEMENT + STAFF) -->
        @if(in_array(auth()->user()->role, ['admin','management','staff']))
            <a href="{{ route('gallery.index') }}" class="block hover:text-yellow-300">
                🖼️ Gallery
            </a>
        @endif

    </nav>

    <!-- ROLE DISPLAY -->
    <div class="mt-auto text-xs text-white/60 pt-6">
        Logged in as<br>
        <span class="text-white font-semibold capitalize">
            {{ auth()->user()->role }}
        </span>
    </div>

    <!-- LOGOUT -->
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit"
            class="w-full text-left text-sm px-3 py-2 rounded-lg
                   bg-red-500/20 hover:bg-red-500/30 text-red-200">
            🚪 Logout
        </button>
    </form>

</aside>