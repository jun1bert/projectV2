<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #faf7f2;
}

h1,h2,h3 {
    font-family: 'Playfair Display', serif;
}

.glass {
    background: rgba(255,255,255,0.65);
    backdrop-filter: blur(18px);
    border: 1px solid rgba(255,255,255,0.4);
}

.sidebar {
    background: linear-gradient(180deg, #2b1f1a, #3a2a22);
}

.gold { color: #c8a96a; }

#sidebar {
    transition: transform .3s ease;
}

/* pagination */
.pagination {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 20px;
}

/* Responsive table/cards toggle */
#desktopTable { display: none !important; }
#mobileCards  { display: block !important; }
@media (min-width: 768px) {
    #desktopTable { display: block !important; }
    #mobileCards  { display: none !important; }
}

/* Fix: prevent flex parent from overriding visibility */
main > * {
    flex-shrink: 0;
}
</style>
</head>

<body class="overflow-x-hidden bg-[#faf7f2] pt-14 md:pt-0">

<!-- MOBILE TOP BAR -->
<div class="md:hidden fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-4 py-3 bg-white/80 backdrop-blur border-b">
    <button onclick="toggleSidebar()" class="text-2xl">☰</button>
</div>

<!-- OVERLAY -->
<div id="overlay"
     class="fixed inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden z-40"
     onclick="toggleSidebar()"></div>

<div class="md:flex">

    @include('layouts.partials.sidebar')

    <!-- MAIN CONTENT -->
    <div class="flex-1 md:ml-64 w-full min-h-screen">

        <!-- HEADER -->
        <header class="glass border-b px-4 md:px-6 py-5">
            <h2 class="text-xl md:text-2xl font-semibold">
                @yield('header', 'Dashboard Overview')
            </h2>
            <p class="text-sm text-gray-500">
                @yield('subheader', 'Luxury Spa Management System')
            </p>
        </header>

        <!-- CONTENT -->
        <main class="p-4 md:p-6">
            @yield('content')
        </main>

    </div>
</div>

<!-- SCRIPTS -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    if (!sidebar || !overlay) return;

    const isHidden = sidebar.classList.contains('-translate-x-full');

    if (isHidden) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0');
        overlay.classList.add('pointer-events-auto');
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0');
        overlay.classList.remove('pointer-events-auto');
    }
}
</script>

@stack('scripts')

</body>
</html>