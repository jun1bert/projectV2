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
    </style>
</head>

<body id="top">

<!-- NAV -->
<header class="fixed w-full top-0 z-50">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 lg:px-10 py-5">

        <h1 class="text-lg sm:text-xl font-bold text-[var(--gold)]">
            <a href="#top">Martinis & Manicures</a>
        </h1>

        <nav class="flex gap-5 text-sm">
            <a href="#services" class="text-white/70 hover:text-[var(--gold)]">Services</a>
            <a href="#gallery" class="text-white/70 hover:text-[var(--gold)]">Gallery</a>
            <a href="#book" class="bg-[var(--gold)] text-black px-5 py-2 rounded-full">Book Now</a>
        </nav>

    </div>
</header>

<!-- HERO -->
<section class="pt-36 pb-20 text-center px-4">
    <p class="text-xs tracking-[0.25em] uppercase text-white/50">
        Luxury Wellness Experience
    </p>

    <h1 class="luxury-title mt-6 font-semibold">
        Beauty is a Ritual,<br>Not a Routine
    </h1>
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

        @foreach($services as $service)
            <div class="glass p-6 rounded-3xl">
                <h3 class="text-xl">{{ $service->name }}</h3>

                @if($service->price_min && $service->price_max)
                    <p class="text-white/50 mt-2">
                        ₱{{ $service->price_min }} – ₱{{ $service->price_max }}
                    </p>
                @endif

                @if($service->description)
                    <p class="text-white/60 mt-3 text-sm">
                        {{ $service->description }}
                    </p>
                @endif
            </div>
        @endforeach

    </div>
</section>

<!-- GALLERY -->
<section id="gallery" class="px-4 py-20">

    <div class="text-center mb-10">
        <h2 class="text-3xl font-light">Our Space</h2>
        <p class="text-white/60 mt-3 text-sm">A sanctuary of calm and beauty</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">

        <div class="h-40 bg-white/5 rounded-3xl"></div>
        <div class="h-32 bg-white/10 rounded-3xl"></div>
        <div class="h-40 bg-white/5 rounded-3xl"></div>
        <div class="h-32 bg-white/10 rounded-3xl"></div>

        <div class="h-40 bg-white/5 rounded-3xl col-span-2"></div>
        <div class="h-32 bg-white/10 rounded-3xl"></div>

    </div>
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