<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | @yield('title', 'Something went wrong')</title>
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
            background:
                linear-gradient(135deg, rgba(250,249,246,.98), rgba(230,218,200,.9)),
                radial-gradient(circle at 12% 10%, rgba(164,141,120,.22), transparent 32%),
                radial-gradient(circle at 88% 20%, rgba(203,185,164,.28), transparent 34%);
            color: var(--ink);
            font-family: Inter, Arial, sans-serif;
        }

        h1 {
            font-family: Georgia, serif;
            letter-spacing: .06em;
        }
    </style>
</head>
<body class="min-h-screen">
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <section class="w-full max-w-xl rounded-3xl border border-[var(--desert-rock)]/20 bg-[var(--feather-white)]/88 p-8 text-center shadow-2xl shadow-[#4d4037]/10 backdrop-blur md:p-10">
            <p class="text-xs font-bold uppercase tracking-[.28em] text-[var(--desert-rock)]">@yield('code')</p>
            <h1 class="mt-4 text-3xl font-bold text-[var(--ink)] md:text-4xl">@yield('heading')</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-7 text-[var(--muted)]">@yield('message')</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ url('/') }}"
                   class="rounded-xl bg-[var(--desert-rock)] px-5 py-3 text-sm font-bold text-[var(--feather-white)] transition hover:bg-[#8f7663]">
                    Go Home
                </a>

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="rounded-xl border border-[var(--desert-rock)]/35 bg-[var(--feather-white)] px-5 py-3 text-sm font-bold text-[var(--ink)] transition hover:bg-[var(--creamed-oat)]/60">
                        Dashboard
                    </a>
                @endauth
            </div>
        </section>
    </main>
</body>
</html>
