<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Martinis & Manicures</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: Inter, sans-serif;
}
</style>
</head>

<body class="min-h-screen overflow-hidden">

<div class="min-h-screen flex items-center justify-center relative"
     style="background: linear-gradient(135deg,#120d0b,#2b1f1a,#3a2a22);">

    <!-- glow background -->
    <div class="absolute inset-0">
        <div class="absolute w-[600px] h-[600px] bg-[#c8a96a]/10 blur-3xl rounded-full -top-40 -left-40 animate-pulse"></div>
        <div class="absolute w-[500px] h-[500px] bg-white/5 blur-3xl rounded-full bottom-0 right-0"></div>
    </div>

    <!-- CARD -->
    <div class="relative w-full max-w-md px-6">

        <div class="backdrop-blur-2xl bg-white/10 border border-white/20
                    shadow-2xl rounded-3xl p-8">

            <!-- BRAND -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-semibold text-white"
                    style="font-family: Playfair Display;">
                    Martinis & Manicures
                </h1>

                <p class="text-sm text-white/60 mt-2">
                    Luxury Spa Management System
                </p>

                <div class="w-14 h-[2px] bg-[#c8a96a] mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- STATUS -->
            @if (session('status'))
                <div class="mb-4 text-sm text-white/70">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- EMAIL -->
                <div>
                    <label class="text-white/70 text-sm">Email</label>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           class="mt-1 w-full rounded-xl bg-white/10 border border-white/20 text-white
                                  px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]">
                    @error('email')
                        <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="text-white/70 text-sm">Password</label>

                    <input type="password"
                           name="password"
                           required
                           class="mt-1 w-full rounded-xl bg-white/10 border border-white/20 text-white
                                  px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#c8a96a]">

                    @error('password')
                        <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- OPTIONS -->
                <div class="flex items-center justify-between text-sm">

                    <label class="flex items-center gap-2 text-white/60">
                        <input type="checkbox"
                               name="remember"
                               class="rounded border-white/30 bg-white/10 text-[#c8a96a] focus:ring-[#c8a96a]">
                        Remember me
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-white/50 hover:text-[#c8a96a]">
                            Forgot?
                        </a>
                    @endif

                </div>

                <!-- BUTTON -->
                <button type="submit"
                        class="w-full bg-[#c8a96a] hover:bg-[#b89455]
                               text-black font-medium py-2.5 rounded-xl
                               transition hover:scale-[1.02]">
                    Sign in
                </button>

            </form>
        </div>

        <!-- FOOTER -->
        <p class="text-center text-xs text-white/40 mt-6">
            Secure access • Martinis & Manicures © {{ date('Y') }}
        </p>

    </div>
</div>

</body>
</html>