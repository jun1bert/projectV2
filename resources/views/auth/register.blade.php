<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Martinis & Manicures</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

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
    font-family: 'Inter', sans-serif;
    background:
        linear-gradient(135deg, rgba(244,241,234,.98), rgba(230,218,200,.92)),
        radial-gradient(circle at 12% 8%, rgba(164,141,120,.24), transparent 30%),
        radial-gradient(circle at 88% 18%, rgba(203,185,164,.3), transparent 34%);
    color: var(--ink);
}

h1 {
    font-family: 'Playfair Display', serif;
}

.auth-card {
    background:
        linear-gradient(135deg, rgba(250,249,246,.92), rgba(244,241,234,.84)),
        radial-gradient(circle at 100% 0%, rgba(164,141,120,.12), transparent 32%);
    border: 1px solid rgba(164, 141, 120, .24);
    box-shadow: 0 24px 70px rgba(77, 64, 55, .14);
    backdrop-filter: blur(18px);
}

.auth-field {
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(164, 141, 120, .25);
    background: rgba(250, 249, 246, .92);
    color: var(--ink);
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.auth-field:focus {
    border-color: var(--desert-rock);
    box-shadow: 0 0 0 4px rgba(164, 141, 120, .16);
    background: #fff;
}
</style>
</head>

<body class="min-h-screen">
<main class="flex min-h-screen items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/martinis-logo.png') }}" alt="Martinis and Manicures" class="mx-auto h-20 w-auto object-contain">
        </div>

        <div class="auth-card rounded-2xl p-6 sm:p-8">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-semibold text-[var(--ink)]">Create Account</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">Join the spa management system.</p>
                <div class="mx-auto mt-4 h-0.5 w-14 rounded-full bg-[var(--desert-rock)]"></div>
            </div>

            @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" required class="auth-field px-4 py-3 text-sm">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" required class="auth-field px-4 py-3 text-sm">
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Mobile Number (09XXXXXXXXX)" required class="auth-field px-4 py-3 text-sm">
                <input type="password" name="password" placeholder="Password" required class="auth-field px-4 py-3 text-sm">
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="auth-field px-4 py-3 text-sm">

                <button type="submit"
                        class="w-full rounded-xl bg-[var(--desert-rock)] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#927865]">
                    Create Account
                </button>

                <p class="text-center text-sm text-[var(--muted)]">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-[var(--desert-rock)] hover:underline">Sign in</a>
                </p>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-[var(--muted)]">
            Secure registration - Martinis and Manicures {{ date('Y') }}
        </p>
    </div>
</main>
</body>
</html>
