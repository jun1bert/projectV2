<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Martinis & Manicures</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --gold: #c8a96a;
            --gold-dark: #b89455;
            --bg-1: #120d0b;
            --bg-2: #2b1f1a;
            --bg-3: #3a2a22;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-1), var(--bg-2), var(--bg-3));
            color: white;
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }

        .luxury-title {
            font-size: clamp(2.2rem, 5vw, 5rem);
            line-height: 1.05;
        }

        .glass {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .btn-gold {
            background: var(--gold);
            color: #0b0b0b;
            font-weight: 500;
        }

        .btn-gold:hover {
            background: var(--gold-dark);
        }

        @keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-up {
    animation: fadeUp 0.7s ease both;
}

html {
    scroll-behavior: smooth;
}

.hero-gold {
    position: relative;

    background: linear-gradient(
        180deg,
        #fff8d6 0%,
        #f7d87b 20%,
        #c8a96a 50%,
        #fff2b0 75%,
        #ffffff 100%
    );

    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

    text-shadow:
        0 0 10px rgba(200,169,106,.4),
        0 0 20px rgba(200,169,106,.3),
        0 0 40px rgba(200,169,106,.25);

    animation: goldGlow 4s ease-in-out infinite;
}

@keyframes goldGlow {
    0%,100%{
        filter: brightness(1);
    }
    50%{
        filter: brightness(1.25);
    }
}

.hero-gold::after {
    content: "";

    position: absolute;
    inset: 0;

    background: linear-gradient(
        110deg,
        transparent 30%,
        rgba(255,255,255,.9) 50%,
        transparent 70%
    );

    background-size: 200% 100%;

    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

    animation: shimmer 6s linear infinite;
}

@keyframes shimmer {
    from {
        background-position: -200% 0;
    }
    to {
        background-position: 200% 0;
    }
}

.title-glitters {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.title-glitters span {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #fff8cf;
    border-radius: 50%;
    box-shadow:
        0 0 5px #fff,
        0 0 10px var(--gold),
        0 0 18px var(--gold);
    animation: starTwinkle 2.8s ease-in-out infinite;
}

.title-glitters span::before,
.title-glitters span::after {
    content: "";
    position: absolute;
    background: #fff8cf;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
}

.title-glitters span::before {
    width: 1px;
    height: 10px;
}

.title-glitters span::after {
    width: 10px;
    height: 1px;
}

.title-glitters span:nth-child(1){ top: 18%; left: 20%; }
.title-glitters span:nth-child(2){ top: 35%; left: 44%; animation-delay: .6s; }
.title-glitters span:nth-child(3){ top: 20%; left: 70%; animation-delay: 1.1s; }
.title-glitters span:nth-child(4){ top: 68%; left: 34%; animation-delay: 1.6s; }
.title-glitters span:nth-child(5){ top: 60%; left: 82%; animation-delay: 2.1s; }

@keyframes starTwinkle {
    0%, 100% {
        opacity: 0;
        transform: scale(.4) rotate(0deg);
    }
    50% {
        opacity: 1;
        transform: scale(1) rotate(45deg);
    }
}

.bg-aurora {
    position: fixed;
    inset: -20%;
    z-index: -3;
    pointer-events: none;
    background:
        radial-gradient(circle at 20% 30%, rgba(200,169,106,.16), transparent 26%),
        radial-gradient(circle at 80% 20%, rgba(255,226,150,.10), transparent 24%),
        radial-gradient(circle at 50% 80%, rgba(200,169,106,.12), transparent 30%);
    filter: blur(8px);
    animation: auroraMove 18s ease-in-out infinite alternate;
}

@keyframes auroraMove {
    from {
        transform: translate3d(-2%, -1%, 0) scale(1);
    }
    to {
        transform: translate3d(2%, 3%, 0) scale(1.08);
    }
}

.bg-dust {
    position: fixed;
    inset: 0;
    z-index: -2;
    pointer-events: none;
    overflow: hidden;
}

.bg-dust span {
    position: absolute;
    width: 3px;
    height: 3px;
    background: rgba(255, 220, 140, .85);
    border-radius: 999px;
    box-shadow: 0 0 12px rgba(200,169,106,.9);
    animation: dustFloat 9s ease-in-out infinite;
}

.bg-dust span:nth-child(11) { top: 8%; left: 25%; animation-delay: 1.2s; }
.bg-dust span:nth-child(12) { top: 12%; left: 55%; animation-delay: 2.5s; }
.bg-dust span:nth-child(13) { top: 20%; left: 75%; animation-delay: 3.8s; }
.bg-dust span:nth-child(14) { top: 25%; left: 5%; animation-delay: 4.2s; }
.bg-dust span:nth-child(15) { top: 32%; left: 40%; animation-delay: 5.3s; }
.bg-dust span:nth-child(16) { top: 38%; left: 62%; animation-delay: 1.7s; }
.bg-dust span:nth-child(17) { top: 42%; left: 15%; animation-delay: 6.1s; }
.bg-dust span:nth-child(18) { top: 50%; left: 78%; animation-delay: 7.5s; }
.bg-dust span:nth-child(19) { top: 55%; left: 48%; animation-delay: 2.8s; }
.bg-dust span:nth-child(20) { top: 60%; left: 25%; animation-delay: 8.2s; }

.bg-dust span:nth-child(21) { top: 66%; left: 85%; animation-delay: 3.3s; }
.bg-dust span:nth-child(22) { top: 72%; left: 55%; animation-delay: 6.8s; }
.bg-dust span:nth-child(23) { top: 78%; left: 22%; animation-delay: 4.7s; }
.bg-dust span:nth-child(24) { top: 82%; left: 72%; animation-delay: 2.1s; }
.bg-dust span:nth-child(25) { top: 90%; left: 35%; animation-delay: 7.9s; }

.bg-dust span:nth-child(26) { top: 5%; left: 92%; animation-delay: 5.1s; }
.bg-dust span:nth-child(27) { top: 18%; left: 35%; animation-delay: 1.9s; }
.bg-dust span:nth-child(28) { top: 30%; left: 88%; animation-delay: 4.9s; }
.bg-dust span:nth-child(29) { top: 45%; left: 52%; animation-delay: 8.4s; }
.bg-dust span:nth-child(30) { top: 68%; left: 8%; animation-delay: 3.6s; }

@keyframes dustFloat {
    0%, 100% {
        opacity: .15;
        transform: translateY(0) scale(.7);
    }
    50% {
        opacity: .9;
        transform: translateY(-28px) scale(1.15);
    }
}

.bg-dust span:nth-child(3n) {
    width: 2px;
    height: 2px;
}

.bg-dust span:nth-child(4n) {
    width: 4px;
    height: 4px;
}

.bg-dust span:nth-child(5n) {
    width: 5px;
    height: 5px;
}
    </style>
</head>

<body id="top" class="relative overflow-x-hidden">
<div class="bg-aurora"></div>
<div class="bg-dust">
    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>

    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>

    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>
</div>
<!-- NAV -->
<header class="fixed w-full top-0 z-50 bg-[#120d0b]/70 backdrop-blur-xl border-b border-white/10">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-10 py-4">

        <h1 class="text-base sm:text-xl font-bold text-[var(--gold)] whitespace-nowrap">
            <a href="#top">Martinis & Manicures</a>
        </h1>

        <nav class="flex items-center gap-3 sm:gap-6 text-xs sm:text-sm">
            <a href="#services" class="text-white/70 hover:text-[var(--gold)] transition">Services</a>
            <a href="#gallery" class="text-white/70 hover:text-[var(--gold)] transition">Gallery</a>
            <a href="#book" class="bg-[var(--gold)] hover:bg-[var(--gold-dark)] text-black px-3 sm:px-5 py-2 rounded-full transition whitespace-nowrap">
                Book Now
            </a>
        </nav>

    </div>
</header>

<section class="relative pt-32 sm:pt-40 pb-20 text-center px-4 overflow-hidden">
    <p class="text-[10px] sm:text-xs tracking-[0.25em] uppercase text-white/50">
        A refined escape for beauty and self-care.<br>
From flawless nails to rejuvenating treatments,<br>
we pamper you from head to toe.
    </p>

    <div class="relative inline-block mt-6">
        <h1 class="luxury-title hero-gold font-semibold">
            Beauty is a Ritual,<br>
            Not a Routine
        </h1>

        <div class="title-glitters">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</section>

<!-- EXPERIENCE -->
<section class="px-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="glass p-6 rounded-2xl">
            <p class="text-xs text-white/50">Relaxation</p>
            <h3 class="text-lg mt-2">Therapeutic Massage</h3>
        </div>

        <div class="glass p-6 rounded-2xl">
            <p class="text-xs text-white/50">Beauty</p>
            <h3 class="text-lg mt-2">Nail Studio</h3>
        </div>

        <div class="glass p-6 rounded-2xl">
            <p class="text-xs text-white/50">Glow</p>
            <h3 class="text-lg mt-2">Facial Care</h3>
        </div>

        <div class="glass p-6 rounded-2xl">
            <p class="text-xs text-white/50">Wellness</p>
            <h3 class="text-lg mt-2">Body Rituals</h3>
        </div>

    </div>
</section>

<!-- SERVICES -->
<section id="services" class="px-4 py-20">

    <div class="text-center mb-10">
        <h2 class="text-3xl font-light">Signature Services</h2>
        <p class="text-white/60 mt-3 text-sm">Crafted for relaxation and beauty</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($services as $service)
            <div class="glass p-6 rounded-3xl">
                <h3 class="text-xl">{{ $service->name }}</h3>

                <div class="flex items-center gap-2 mt-2 text-sm">
                    <span class="text-[var(--gold)] font-semibold">
                        ₱{{ number_format($service->price, 2) }}
                    </span>

                    <span class="text-white/30">•</span>

                    <span class="text-white/50">
                        {{ $service->duration }} mins
                    </span>
                </div>

                @if($service->description)
                    <p class="text-white/60 mt-3 text-sm">
                        {{ $service->description }}
                    </p>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center text-white/40 text-sm">
                No services available yet.
            </div>
        @endforelse
    </div>
</section>

<!-- GALLERY -->
<section id="gallery" class="px-4 py-20">

    <div class="text-center mb-12">
        <h2 class="text-3xl font-light">Our Space</h2>
        <p class="text-white/60 mt-3 text-sm">A sanctuary of calm and beauty</p>
    </div>

    @if($galleryImages->isEmpty())
        <div class="text-center py-10 text-white/40 text-sm">
            No gallery images yet.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 max-w-7xl mx-auto">

            @foreach($galleryImages as $image)
                <div class="
                    relative overflow-hidden rounded-3xl bg-white/5 group animate-fade-up
                    {{ $loop->first ? 'lg:col-span-2 lg:row-span-2' : '' }}
                ">

                    {{-- Placeholder / loading skeleton --}}
                    <div class="absolute inset-0 bg-white/10 animate-pulse"></div>

                    <img src="{{ asset('storage/' . $image->path) }}"
                         alt="{{ $image->title ?? 'Gallery image' }}"
                         loading="lazy"
                         onload="this.previousElementSibling.style.display='none'; this.classList.remove('opacity-0')"
                         onerror="this.onerror=null; this.src='{{ asset('images/placeholder-gallery.jpg') }}'; this.previousElementSibling.style.display='none'; this.classList.remove('opacity-0')"
                         class="
                            relative z-10 w-full object-cover opacity-0
                            transition-all duration-700 ease-out
                            group-hover:scale-105 group-hover:brightness-75
                            {{ $loop->first ? 'h-[650px]' : 'h-[310px]' }}
                         ">

                    <div class="absolute inset-0 z-10 bg-gradient-to-t from-black/80 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>

                    @if($image->caption || $image->title)
                        <div class="absolute bottom-0 left-0 right-0 z-20 p-6 translate-y-6 opacity-0
                                    group-hover:translate-y-0 group-hover:opacity-100
                                    transition-all duration-300">

                            @if($image->title)
                                <h3 class="text-white text-xl font-medium">
                                    {{ $image->title }}
                                </h3>
                            @endif

                            @if($image->caption)
                                <p class="text-white/75 text-sm mt-2">
                                    {{ $image->caption }}
                                </p>
                            @endif

                        </div>
                    @endif

                </div>
            @endforeach

        </div>
    @endif

</section>

<!-- BOOKING -->
<section id="book" class="px-4 py-20">

    <div class="max-w-3xl mx-auto glass rounded-2xl p-6 sm:p-10">

        <h2 class="text-3xl text-center mb-8">Reserve Your Experience</h2>

        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-500/20 text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-500/20 text-red-300">
                <ul class="text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="bookingForm" method="POST" action="{{ route('appointments.store') }}" class="space-y-5">
            @csrf

            <input type="text" name="full_name" placeholder="Full Name"
                class="w-full p-4 bg-white/5 border border-white/10 rounded-xl text-white">

            <input type="text" name="contact_number" placeholder="Contact Number"
                class="w-full p-4 bg-white/5 border border-white/10 rounded-xl text-white">

            <!-- FIXED -->
            <select name="service_id"
                class="w-full p-4 bg-white/5 border border-white/10 rounded-xl text-white">

                <option value="">Select Service</option>

                @foreach($services as $service)
                    <option value="{{ $service->id }}">
                        {{ $service->name }}
                    </option>
                @endforeach

            </select>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input type="date" name="date"
                    class="p-4 bg-white/5 border border-white/10 rounded-xl text-white">

                <select name="time"
                    class="p-4 bg-white/5 border border-white/10 text-white rounded-xl">

                    <option value="">Select Time</option>
                    <option value="08:00">8:00 AM</option>
                    <option value="09:00">9:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="12:00">12:00 PM</option>
                    <option value="13:00">1:00 PM</option>
                    <option value="14:00">2:00 PM</option>
                    <option value="15:00">3:00 PM</option>
                    <option value="16:00">4:00 PM</option>
                    <option value="17:00">5:00 PM</option>

                </select>
            </div>

            <textarea name="notes" placeholder="Special Requests"
                class="w-full p-4 bg-white/5 border border-white/10 rounded-xl text-white"></textarea>

            <button type="button" onclick="openConfirmModal()"
                class="w-full btn-gold py-3 rounded-xl">
                Submit Appointment Request
            </button>
        </form>
    </div>
</section>

<!-- MODAL -->
<div id="confirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60">

    <div class="glass rounded-2xl w-[90%] max-w-lg p-6">

        <h2 class="text-2xl mb-4">Confirm Appointment</h2>

        <div class="space-y-2 text-sm text-white/70">
            <p><strong>Name:</strong> <span id="previewName"></span></p>
            <p><strong>Contact:</strong> <span id="previewContact"></span></p>
            <p><strong>Service:</strong> <span id="previewService"></span></p>
            <p><strong>Date:</strong> <span id="previewDate"></span></p>
            <p><strong>Time:</strong> <span id="previewTime"></span></p>
            <p><strong>Notes:</strong> <span id="previewNotes"></span></p>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeConfirmModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
            <button onclick="confirmSubmit()" class="btn-gold px-4 py-2 rounded-lg">Confirm</button>
        </div>

    </div>
</div>

<!-- SCRIPT FIXED -->
<script>
function formatTime(time) {
    if (!time) return '';
    let [h, m] = time.split(':');
    h = parseInt(h);
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${ampm}`;
}

function openConfirmModal() {
    const name = document.querySelector('[name="full_name"]').value;
    const contact = document.querySelector('[name="contact_number"]').value;

    const serviceSelect = document.querySelector('[name="service_id"]');
    const service = serviceSelect.options[serviceSelect.selectedIndex]?.text;

    const date = document.querySelector('[name="date"]').value;
    const time = document.querySelector('[name="time"]').value;
    const notes = document.querySelector('[name="notes"]').value;

    if (!name || !contact || !serviceSelect.value || !date || !time) {
        alert("Please complete all required fields.");
        return;
    }

    document.getElementById("previewName").textContent = name;
    document.getElementById("previewContact").textContent = contact;
    document.getElementById("previewService").textContent = service;
    document.getElementById("previewDate").textContent = date;
    document.getElementById("previewTime").textContent = formatTime(time);
    document.getElementById("previewNotes").textContent = notes || "None";

    document.getElementById("confirmModal").classList.remove("hidden");
}

function closeConfirmModal() {
    document.getElementById("confirmModal").classList.add("hidden");
}

function confirmSubmit() {
    document.getElementById("confirmModal").classList.add("hidden");
    setTimeout(() => {
        document.getElementById("bookingForm").requestSubmit();
    }, 150);
}
</script>

</body>
</html>