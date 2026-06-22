<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Martinis & Manicures</title>

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
        <div class="absolute w-[600px] h-[600px] bg-[#c8a96a]/10 blur-3xl rounded-full -top-40 -left-40"></div>
        <div class="absolute w-[500px] h-[500px] bg-white/5 blur-3xl rounded-full bottom-0 right-0"></div>
    </div>

    <!-- CARD -->
    <div class="relative w-full max-w-md px-6">

        <div class="backdrop-blur-2xl bg-white/10 border border-white/20
                    shadow-2xl rounded-3xl p-8">

            <!-- TITLE -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-white"
                    style="font-family: Playfair Display;">
                    Create Account
                </h1>

                <p class="text-white/60 text-sm mt-2">
                    Join the Luxury Spa Experience
                </p>

                <div class="w-14 h-[2px] bg-[#c8a96a] mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- ERRORS -->
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-300">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- NAME -->
                <div>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Full Name"
                           required
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white
                                  focus:outline-none focus:ring-2 focus:ring-[#c8a96a]">
                </div>

                <!-- EMAIL -->
                <div>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Email Address"
                           required
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white
                                  focus:outline-none focus:ring-2 focus:ring-[#c8a96a]">
                </div>

                <!-- PASSWORD -->
                <div>
                    <input type="password"
                           name="password"
                           placeholder="Password"
                           required
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white
                                  focus:outline-none focus:ring-2 focus:ring-[#c8a96a]">
                </div>

                <!-- CONFIRM PASSWORD -->
                <div>
                    <input type="password"
                           name="password_confirmation"
                           placeholder="Confirm Password"
                           required
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-white
                                  focus:outline-none focus:ring-2 focus:ring-[#c8a96a]">
                </div>

                <!-- BUTTON -->
                <button type="submit"
                        class="w-full bg-[#c8a96a] hover:bg-[#b89455]
                               text-black font-medium py-2.5 rounded-xl
                               transition hover:scale-[1.02]">
                    Create Account
                </button>

                <!-- LOGIN LINK -->
                <p class="text-center text-sm text-white/50 mt-4">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-[#c8a96a] hover:underline">
                        Sign in
                    </a>
                </p>

            </form>
        </div>

        <!-- FOOTER -->
        <p class="text-center text-xs text-white/40 mt-6">
            Secure registration • Martinis & Manicures © {{ date('Y') }}
        </p>

    </div>
</div>

</body>
</html>