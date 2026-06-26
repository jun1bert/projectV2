<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Martinis &amp; Manicures</title>

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
            background:
                linear-gradient(135deg, rgba(244,241,234,.98), rgba(230,218,200,.92)),
                radial-gradient(circle at 12% 8%, rgba(164,141,120,.24), transparent 30%),
                radial-gradient(circle at 88% 18%, rgba(203,185,164,.3), transparent 34%);
            color: var(--ink);
        }

        h1 {
            font-family: 'Black Mango', Georgia, serif;
            letter-spacing: .08em;
        }

        .field {
            border: 1px solid rgba(164, 141, 120, .34);
            background: rgba(250, 249, 246, .92);
            color: var(--ink);
            outline: none;
        }

        .field:focus {
            border-color: var(--desert-rock);
            box-shadow: 0 0 0 3px rgba(164, 141, 120, .18);
        }

        .auth-shell {
            background:
                linear-gradient(135deg, rgba(250,249,246,.92), rgba(244,241,234,.84)),
                radial-gradient(circle at 100% 0%, rgba(164,141,120,.12), transparent 32%);
            border: 1px solid rgba(164,141,120,.24);
            box-shadow: 0 24px 65px rgba(77,64,55,.14);
        }
    </style>
</head>

<body class="min-h-screen overflow-x-hidden">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <section class="auth-shell w-full max-w-md rounded-2xl p-6 backdrop-blur sm:p-8">
            <div class="mb-8 text-center">
                <img src="{{ asset('images/martinis-logo.png') }}"
                     alt="Martinis and Manicures"
                     class="mx-auto h-20 w-auto object-contain">
                <h1 class="mt-5 text-2xl font-bold text-[var(--desert-rock)]">Welcome Back</h1>
                <p class="mt-2 text-sm font-semibold text-[var(--muted)]">Management System</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-[var(--muted)]">Email</label>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           autocomplete="username"
                           class="field w-full rounded-xl px-4 py-3 text-sm">
                    @error('email')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold text-[var(--muted)]">Password</label>
                    <input type="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           class="field w-full rounded-xl px-4 py-3 text-sm">
                    @error('password')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between gap-3 text-sm">
                    <label class="flex items-center gap-2 text-[var(--muted)]">
                        <input type="checkbox"
                               name="remember"
                               class="rounded border-[var(--soft-sandstone)] text-[var(--desert-rock)] focus:ring-[var(--desert-rock)]">
                        Remember me
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="font-bold text-[var(--desert-rock)] hover:text-[#8f7663]">
                            Forgot?
                        </a>
                    @endif
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-[var(--desert-rock)] px-4 py-3 text-sm font-bold text-[var(--feather-white)] transition hover:bg-[#8f7663] active:scale-[.98]">
                    Sign In
                </button>
            </form>

            <p class="mt-6 text-center text-xs font-semibold text-[var(--muted)]">
                Martinis &amp; Manicures &copy; {{ date('Y') }}
            </p>
        </section>
    </main>
</body>
</html>
