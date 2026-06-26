<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ClientFlow</title>

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
        radial-gradient(circle at 88% 18%, rgba(203,185,164,.28), transparent 34%);
    color: var(--ink);
}

h1,h2,h3 {
    font-family: 'Black Mango', Georgia, serif;
    letter-spacing: .06em;
}

.sidebar {
    background:
        linear-gradient(180deg, rgba(250,249,246,.95), rgba(230,218,200,.9)),
        radial-gradient(circle at 30% 0%, rgba(164,141,120,.18), transparent 34%);
}

.gold { color: var(--desert-rock); }

#sidebar { transition: transform .3s ease; }
</style>
</head>

<body>

<div class="flex">

    @include('layouts.partials.sidebar')

    <div class="flex-1 min-h-screen md:ml-64">

        <!-- HEADER -->
        <header class="border-b border-[var(--desert-rock)]/20 bg-[var(--porcelain-mist)]/90 px-6 py-4 shadow-sm backdrop-blur">
            <h2 class="text-xl font-semibold">
                @yield('header')
            </h2>
            <p class="text-sm text-[var(--muted)]">
                @yield('subheader')
            </p>
        </header>

        <!-- CONTENT -->
        <main class="p-6">
            @yield('content')
        </main>

    </div>
</div>

</body>
</html>
