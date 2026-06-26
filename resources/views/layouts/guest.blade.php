<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Martinis & Manicures') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            background:
                linear-gradient(135deg, rgba(244,241,234,.98), rgba(230,218,200,.92)),
                radial-gradient(circle at 12% 8%, rgba(164,141,120,.24), transparent 30%),
                radial-gradient(circle at 88% 18%, rgba(203,185,164,.3), transparent 34%);
            color: var(--ink);
        }

        .guest-card {
            background:
                linear-gradient(135deg, rgba(250,249,246,.92), rgba(244,241,234,.84)),
                radial-gradient(circle at 100% 0%, rgba(164,141,120,.12), transparent 32%);
            border: 1px solid rgba(164,141,120,.24);
            box-shadow: 0 24px 65px rgba(77,64,55,.14);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
        <a href="/" class="mb-6">
            <img src="{{ asset('images/martinis-logo.png') }}"
                 alt="Martinis and Manicures"
                 class="h-20 w-auto object-contain">
        </a>

        <div class="guest-card w-full max-w-md overflow-hidden rounded-2xl px-6 py-5 backdrop-blur sm:px-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
