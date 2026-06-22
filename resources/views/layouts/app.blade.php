<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ClientFlow</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Inter', sans-serif; background: #faf7f2; }
h1,h2,h3 { font-family: 'Playfair Display', serif; }

.sidebar { background: linear-gradient(180deg,#2b1f1a,#3a2a22); }
.gold { color:#c8a96a; }

#sidebar { transition: transform .3s ease; }
</style>
</head>

<body>

<div class="flex">

    @include('layouts.partials.sidebar')

    <div class="flex-1 min-h-screen md:ml-64">

        <!-- HEADER -->
        <header class="bg-white shadow px-6 py-4">
            <h2 class="text-xl font-semibold">
                @yield('header')
            </h2>
            <p class="text-sm text-gray-500">
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