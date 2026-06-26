<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<script src="https://cdn.tailwindcss.com"></script>

<style>
:root {
    --desert-rock: #A48D78;
    --soft-sandstone: #CBB9A4;
    --creamed-oat: #E6DAC8;
    --porcelain-mist: #F4F1EA;
    --feather-white: #FAF9F6;
    --ink: #4d4037;
    --muted: #817267;
}

body {
    font-family: 'Organetto', 'Montserrat', Arial, sans-serif;
    font-weight: 700;
    background:
        linear-gradient(135deg, rgba(244,241,234,.98), rgba(230,218,200,.94)),
        radial-gradient(circle at 12% 8%, rgba(164,141,120,.22), transparent 30%),
        radial-gradient(circle at 88% 18%, rgba(203,185,164,.28), transparent 34%),
        linear-gradient(180deg, rgba(250,249,246,.62), rgba(203,185,164,.18));
    color: var(--ink);
}

h1,h2,h3 {
    font-family: 'Black Mango', Georgia, serif;
    letter-spacing: .06em;
}

.glass {
    background: rgba(250,249,246,0.84);
    backdrop-filter: blur(18px);
    border: 1px solid rgba(164,141,120,0.22);
    box-shadow: 0 16px 45px rgba(77,64,55,.08);
}

.sidebar {
    background:
        linear-gradient(180deg, rgba(244,241,234,.98), rgba(203,185,164,.92)),
        radial-gradient(circle at 30% 0%, rgba(164,141,120,.24), transparent 34%);
}

.gold { color: var(--desert-rock); }

.theme-surface,
.theme-card {
    background: rgba(250,249,246,.88);
    border: 1px solid rgba(164,141,120,.22);
    box-shadow: 0 18px 48px rgba(77,64,55,.09);
}

.theme-panel {
    background:
        linear-gradient(135deg, rgba(250,249,246,.94), rgba(230,218,200,.64)),
        radial-gradient(circle at 95% 0%, rgba(164,141,120,.13), transparent 30%);
    border: 1px solid rgba(164,141,120,.22);
    box-shadow: 0 18px 48px rgba(77,64,55,.08);
}

.theme-field {
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(164,141,120,.3);
    background: rgba(250,249,246,.92);
    color: var(--ink);
    font-size: .875rem;
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.theme-field:focus {
    border-color: var(--desert-rock);
    box-shadow: 0 0 0 4px rgba(164,141,120,.18);
    background: #fff;
}

.theme-button {
    background: var(--desert-rock);
    color: var(--feather-white);
}

.theme-button:hover {
    background: #927865;
}

.muted-button {
    border: 1px solid rgba(164,141,120,.3);
    color: var(--ink);
    background: rgba(250,249,246,.76);
}

.muted-button:hover {
    background: rgba(230,218,200,.55);
}

.theme-table {
    width: 100%;
    font-size: .875rem;
}

.theme-table thead tr {
    background: rgba(203,185,164,.36);
    border-bottom: 1px solid rgba(164,141,120,.24);
    color: var(--muted);
    font-size: .75rem;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.theme-table th {
    padding: .875rem 1.25rem;
    text-align: left;
    font-weight: 800;
}

.theme-table td {
    padding: 1rem 1.25rem;
    vertical-align: top;
}

.theme-table tbody {
    background: rgba(250,249,246,.68);
}

.theme-table tbody tr {
    border-bottom: 1px solid rgba(164,141,120,.14);
}

.theme-table tbody tr:hover {
    background: rgba(230,218,200,.26);
}

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

<body class="overflow-x-hidden pt-14 md:pt-0">

<!-- MOBILE TOP BAR -->
<div class="md:hidden fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-4 py-3 bg-[var(--porcelain-mist)]/92 backdrop-blur border-b border-[var(--desert-rock)]/20 shadow-sm">
    <button onclick="toggleSidebar()" class="rounded-xl border border-[var(--desert-rock)]/20 bg-[var(--creamed-oat)]/70 px-3 py-1.5 text-lg font-bold text-[var(--ink)]">Menu</button>
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
        <header class="glass sticky top-14 z-30 border-b border-[var(--desert-rock)]/20 px-4 py-4 md:top-0 md:px-6 md:py-5">
            <h2 class="text-xl md:text-2xl font-semibold">
                @yield('header', 'Dashboard Overview')
            </h2>
            <p class="text-sm text-[var(--muted)]">
                @yield('subheader', 'Spa Management System')
            </p>
        </header>

        <!-- CONTENT -->
        <main class="p-4 md:p-6 lg:p-8">
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
