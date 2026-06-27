<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Martinis & Manicures</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            isolation: isolate;
            background:
                linear-gradient(135deg, rgba(244,241,234,.98), rgba(230,218,200,.92)),
                radial-gradient(circle at 12% 8%, rgba(164,141,120,.22), transparent 30%),
                radial-gradient(circle at 88% 18%, rgba(203,185,164,.30), transparent 34%);
            color: var(--ink);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                linear-gradient(120deg, transparent 0 18%, rgba(250,249,246,.62) 18% 34%, transparent 34% 100%),
                radial-gradient(circle at 50% 0%, rgba(250,249,246,.75), transparent 42%);
        }

        h1, h2, h3 {
            font-family: 'Black Mango', 'Cinzel', Georgia, serif;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .luxury-title {
            font-size: clamp(2.2rem, 5vw, 5rem);
            line-height: 1.05;
        }

        .glass {
            background: rgba(250,249,246,0.84);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(164,141,120,0.22);
            box-shadow: 0 18px 48px rgba(77,64,55,.10);
        }

        .warm-panel {
            background:
                linear-gradient(135deg, rgba(250,249,246,.92), rgba(230,218,200,.62)),
                radial-gradient(circle at 100% 0%, rgba(164,141,120,.14), transparent 34%);
            border: 1px solid rgba(164,141,120,.22);
            box-shadow: 0 20px 56px rgba(77,64,55,.10);
        }

        .section-band {
            background: rgba(244,241,234,.58);
            border-block: 1px solid rgba(164,141,120,.12);
        }

        .hero-media {
            background-image:
                linear-gradient(90deg, rgba(44,36,31,.74), rgba(77,64,55,.32) 48%, rgba(244,241,234,.82)),
                var(--hero-image);
            background-size: cover;
            background-position: center 38%;
        }

        .hero-photo {
            border: 1px solid rgba(250,249,246,.42);
            box-shadow: 0 22px 60px rgba(44,36,31,.26);
            background: var(--creamed-oat);
        }

        .btn-gold {
            background: var(--desert-rock);
            color: var(--feather-white);
            font-weight: 700;
            letter-spacing: .06em;
        }

        .btn-gold:hover {
            background: #8f7663;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(.96) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        html { scroll-behavior: smooth; }

        .animate-fade-up { animation: fadeUp 0.7s ease both; }

        .hero-gold {
            color: var(--desert-rock);
            text-shadow: 0 10px 30px rgba(164,141,120,.16);
        }

        .form-field {
            background: rgba(250,249,246,.92);
            border: 1px solid rgba(164,141,120,.28);
            color: var(--ink);
        }

        .form-field::placeholder { color: rgba(77,64,55,.48); }

        .form-field:focus {
            outline: 2px solid rgba(164,141,120,.36);
            border-color: var(--desert-rock);
        }

        input.form-field[type="date"],
        input.form-field[type="time"],
        select.form-field {
            color: var(--ink);
            color-scheme: light;
            min-height: 56px;
            -webkit-text-fill-color: var(--ink);
            appearance: auto;
            -webkit-appearance: auto;
        }

        input.form-field[type="date"]::-webkit-date-and-time-value,
        input.form-field[type="time"]::-webkit-date-and-time-value {
            color: var(--ink);
            text-align: left;
        }

        /* Field-level validation states */
        .form-field.field-error {
            border-color: #e05252;
            outline: 2px solid rgba(224,82,82,.22);
        }

        .field-hint {
            font-size: .72rem;
            font-weight: 600;
            margin-top: 4px;
            padding-left: 2px;
        }

        .field-hint.error  { color: #c0392b; }
        .field-hint.ok     { color: #5a7a5a; }

        .gallery-card { cursor: zoom-in; }

        .modal-panel { animation: modalIn .22s ease both; }

        .site-logo {
            height: clamp(2.6rem, 8vw, 4rem);
            width: auto;
            max-width: min(12rem, 52vw);
            object-fit: contain;
        }

        .mini-stat {
            background: rgba(77,64,55,.64);
            border: 1px solid rgba(250,249,246,.22);
            color: var(--porcelain-mist);
            box-shadow: 0 14px 34px rgba(44,36,31,.22);
        }

        .establishment-thumb {
            background: var(--creamed-oat);
            border: 1px solid rgba(164,141,120,.22);
            box-shadow: 0 16px 42px rgba(77,64,55,.10);
        }

        .map-frame {
            filter: saturate(.82) sepia(.08);
        }

        @media (max-width: 640px) {
            h1, h2, h3 { letter-spacing: .045em; }
            .site-logo { max-width: 9rem; }
        }
    </style>
</head>

<body id="top" class="relative overflow-x-hidden">

{{-- Header --}}
<header class="fixed w-full top-0 z-50 bg-[var(--porcelain-mist)]/88 backdrop-blur-xl border-b border-[var(--desert-rock)]/20 shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-10 py-3">
        <a href="#top" class="flex min-w-0 items-center justify-center sm:justify-start" aria-label="Martinis and Manicures home">
            <img src="{{ asset('images/martinis-logo-white.png') }}"
                 alt="Martinis and Manicures"
                 class="site-logo">
        </a>

        <nav class="flex items-center justify-end gap-3 sm:gap-6 text-[11px] sm:text-sm">
            <a href="#services" class="hidden text-[var(--feather-white)] hover:text-[var(--creamed-oat)] transition whitespace-nowrap sm:inline">Services</a>
            <a href="#gallery"  class="hidden text-[var(--feather-white)] hover:text-[var(--creamed-oat)] transition whitespace-nowrap sm:inline">Gallery</a>
            <a href="#location" class="hidden text-[var(--feather-white)] hover:text-[var(--creamed-oat)] transition whitespace-nowrap sm:inline">Location</a>
            <a href="#book"
               class="bg-[var(--desert-rock)] hover:bg-[#8f7663] text-[var(--feather-white)]
                      px-3.5 sm:px-5 py-2 rounded-full transition whitespace-nowrap font-bold shrink-0">
                Book
            </a>
        </nav>
    </div>
</header>

{{-- Hero --}}
@php
    $heroImage = asset('images/image8.jpeg');
@endphp
<section class="hero-media relative min-h-[92vh] px-4 pt-28 sm:pt-32"
         style="--hero-image: url('{{ $heroImage }}');">
    <div class="mx-auto grid min-h-[calc(92vh-7rem)] max-w-7xl items-center gap-10 lg:grid-cols-[1fr_420px]">
        <div class="max-w-2xl py-16 text-[var(--feather-white)] sm:py-24">
            <p class="text-[10px] sm:text-xs tracking-[0.28em] uppercase text-[var(--porcelain-mist)]">
                Martinis and Manicures
            </p>
            <h1 class="luxury-title mt-5 font-semibold text-[var(--feather-white)]">
                Beauty is a Ritual,<br>
                Not a Routine
            </h1>
            <p class="mt-6 max-w-xl text-sm leading-7 text-[var(--porcelain-mist)] sm:text-base">
                A calm beauty space for nails, body care, and restorative treatments. Reserve your visit online and let the team prepare your service.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#book" class="btn-gold inline-flex justify-center rounded-full px-7 py-3 text-sm transition active:scale-[.98]">
                    Book Appointment
                </a>
                <a href="#gallery" class="inline-flex justify-center rounded-full border border-[var(--feather-white)]/50 bg-[var(--feather-white)]/12 px-7 py-3 text-sm text-[var(--feather-white)] backdrop-blur transition hover:bg-[var(--feather-white)]/20">
                    View Gallery
                </a>
            </div>
            <div class="mt-10 grid max-w-xl grid-cols-3 gap-3 text-center text-xs text-[var(--porcelain-mist)]">
                <div class="mini-stat rounded-2xl px-3 py-4 backdrop-blur">
                    <strong class="block text-lg text-[var(--feather-white)]">{{ $services->count() }}</strong>
                    Services
                </div>
                <div class="mini-stat rounded-2xl px-3 py-4 backdrop-blur">
                    <strong class="block text-lg text-[var(--feather-white)]">10</strong>
                    Daily slots
                </div>
                <div class="mini-stat rounded-2xl px-3 py-4 backdrop-blur">
                    <strong class="block text-lg text-[var(--feather-white)]">Online/ Walk-In</strong>
                    Booking
                </div>
            </div>
        </div>

        <div class="relative hidden min-h-[560px] lg:block">
            <img src="{{ asset('images/3.jpg') }}" alt="Manicure station at Martinis and Manicures"
                 class="hero-photo absolute right-0 top-4 h-[420px] w-72 rounded-2xl object-cover">
            <img src="{{ asset('images/image11.jpeg') }}" alt="Body care treatment room at Martinis and Manicures"
                 class="hero-photo absolute left-0 top-36 h-80 w-56 rounded-2xl object-cover">
            <img src="{{ asset('images/4.jpg') }}" alt="Comfortable pedicure lounge at Martinis and Manicures"
                 class="hero-photo absolute bottom-0 right-16 h-56 w-48 rounded-2xl object-cover">
        </div>
    </div>
</section>

{{-- Establishment preview --}}
<section class="px-4 py-12">
    <div class="mx-auto grid max-w-7xl gap-4 sm:grid-cols-5">
        @foreach([
            ['src' => asset('images/image1.jpeg'), 'alt' => 'Main salon and manicure area'],
            ['src' => asset('images/image9.jpeg'), 'alt' => 'Martinis and Manicures reception and refreshment bar'],
            ['src' => asset('images/image12.jpeg'), 'alt' => 'Pedicure lounge with reclining chairs'],
            ['src' => asset('images/image6.jpeg'), 'alt' => 'Private body care treatment room'],
            ['src' => asset('images/image14.jpeg'), 'alt' => 'Private treatment corridor at Martinis and Manicures'],
        ] as $photo)
            <img src="{{ $photo['src'] }}"
                 alt="{{ $photo['alt'] }}"
                 loading="lazy"
                 class="establishment-thumb h-64 w-full rounded-2xl object-cover sm:h-72 lg:h-80">
        @endforeach
    </div>
</section>

{{-- Services --}}
<section id="services" class="section-band px-4 py-20">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-light">Signature Services</h2>
        <p class="text-[var(--muted)] mt-3 text-sm">Clear choices, calm pacing, no guesswork.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-7xl mx-auto">
        @forelse($services as $service)
            <div class="warm-panel flex min-h-[210px] flex-col rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <h3 class="text-xl">{{ $service->name }}</h3>
                    @if($service->requires_consent)
                    <span class="shrink-0 rounded-full bg-[rgba(164,141,120,.14)] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[var(--desert-rock)]">
                        Consent
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-2 text-sm">
                    <span class="text-[var(--desert-rock)] font-semibold">
                        &#8369;{{ number_format($service->price, 2) }}
                    </span>
                    <span class="text-[var(--soft-sandstone)]">&bull;</span>
                    <span class="text-[var(--muted)]">{{ $service->duration }} mins</span>
                </div>
                @if($service->description)
                    <p class="text-[var(--muted)] mt-3 text-sm leading-relaxed">
                        {{ $service->description }}
                    </p>
                @endif
                <a href="#book" class="mt-auto pt-5 text-sm font-bold text-[var(--desert-rock)] hover:text-[#8f7663]">
                    Book this service
                </a>
            </div>
        @empty
            <div class="col-span-full text-center text-[var(--muted)] text-sm py-10">
                No services available yet.
            </div>
        @endforelse
    </div>
</section>

{{-- Gallery --}}
<section id="gallery" class="px-4 py-20">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-light">Our Space</h2>
        <p class="text-[var(--muted)] mt-3 text-sm">A sanctuary of calm and beauty</p>
    </div>

    @if($galleryImages->isEmpty())
        <div class="text-center py-10 text-[var(--muted)] text-sm">No gallery images yet.</div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 max-w-7xl mx-auto">
            @foreach($galleryImages as $image)
                @php
                    $galleryUrl = $image->url;
                @endphp
                <button type="button"
                    class="gallery-card relative overflow-hidden rounded-2xl bg-[var(--creamed-oat)] group animate-fade-up w-full text-left shadow-xl shadow-[#4d4037]/10
                           {{ $loop->first ? 'lg:col-span-2 lg:row-span-2' : '' }}"
                    @if($image->exists_on_disk) onclick="openGalleryModal(this)" @endif
                    data-gallery-src="{{ $galleryUrl }}"
                    data-gallery-title="{{ $image->title ?? 'Gallery image' }}"
                    data-gallery-caption="{{ $image->caption ?? '' }}">

                    <div class="absolute inset-0 bg-[var(--soft-sandstone)]/35 animate-pulse"></div>

                    @if($image->exists_on_disk)
                        <img src="{{ $galleryUrl }}"
                             alt="{{ $image->title ?? 'Gallery image' }}"
                             loading="lazy"
                             onload="this.previousElementSibling.style.display='none'; this.classList.remove('opacity-0')"
                             class="relative z-10 w-full object-cover opacity-0
                                    transition-all duration-700 ease-out
                                    group-hover:scale-105 group-hover:brightness-90
                                    {{ $loop->first ? 'h-[420px] sm:h-[520px] lg:h-[650px]' : 'h-[260px] sm:h-[310px]' }}">
                    @else
                        <div class="relative z-10 grid w-full place-items-center bg-[var(--creamed-oat)] p-6 text-center {{ $loop->first ? 'h-[420px] sm:h-[520px] lg:h-[650px]' : 'h-[260px] sm:h-[310px]' }}">
                            <div>
                                <p class="text-sm font-bold text-[var(--ink)]">Gallery image missing</p>
                                <p class="mt-2 break-all text-xs leading-5 text-[var(--muted)]">{{ $image->path }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="absolute inset-0 z-10 bg-gradient-to-t from-[#4d4037]/75 via-[#4d4037]/8 to-transparent
                                opacity-0 group-hover:opacity-100 transition duration-300"></div>

                    @if($image->caption || $image->title)
                        <div class="absolute bottom-0 left-0 right-0 z-20 p-6
                                    translate-y-6 opacity-0
                                    group-hover:translate-y-0 group-hover:opacity-100
                                    transition-all duration-300">
                            @if($image->title)
                                <h3 class="text-[var(--feather-white)] text-xl font-medium">{{ $image->title }}</h3>
                            @endif
                            @if($image->caption)
                                <p class="text-[var(--porcelain-mist)] text-sm mt-2">{{ $image->caption }}</p>
                            @endif
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    @endif
</section>

{{-- Contact & Location --}}
<section id="location" class="px-4 py-20">
    <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-stretch">

        {{-- Contact Information --}}
        <div class="warm-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[.22em] text-[var(--desert-rock)]">
                Visit Us
            </p>

            <h2 class="mt-4 text-3xl">
                Contact & Location
            </h2>

            <p class="mt-4 text-sm leading-7 text-[var(--muted)]">
                Visit Martinis & Manicures on the
                <strong>2nd Floor of Alvarez Building</strong>,
                directly above <strong>FICOBank</strong> in
                Solano, Nueva Vizcaya.
            </p>

            <div class="mt-6 space-y-4">

                <div class="rounded-2xl bg-[var(--feather-white)]/70 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">
                        Contact Number
                    </p>

                    <a href="tel:+639190943803"
                       class="mt-1 block text-xl font-bold text-[var(--ink)] hover:text-[var(--desert-rock)]">
                        0919 094 3803
                    </a>
                </div>

                <div class="rounded-2xl bg-[var(--feather-white)]/70 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">
                        Address
                    </p>

                    <p class="mt-2 text-sm leading-6 text-[var(--ink)]">
                        2nd Floor, Alvarez Building<br>
                        Above FICOBank<br>
                        Corner Burgos & Luna Street<br>
                        Brgy. Quirino, Solano, Nueva Vizcaya 3709
                    </p>
                </div>

            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">

                <a href="tel:+639190943803"
                   class="btn-gold rounded-xl px-4 py-3 text-center">
                    Call Now
                </a>

                <a href="https://www.google.com/maps/dir/?api=1&destination=16.514140308362894,121.18155211569338&travelmode=driving"
                   target="_blank"
                   rel="noopener"
                   class="rounded-xl border border-[var(--desert-rock)]/35 bg-[var(--feather-white)]/70 px-4 py-3 text-center font-semibold text-[var(--ink)] transition hover:bg-[var(--creamed-oat)]">
                    Get Directions
                </a>

            </div>
        </div>

        {{-- Storefront Photo --}}
        <div class="warm-panel overflow-hidden rounded-2xl p-3">

            <img
                src="{{ asset('images/location3.jpg') }}"
                alt="Martinis & Manicures storefront"
                class="h-[360px] w-full rounded-xl object-cover sm:h-[440px] lg:h-full">

            <div class="mt-4 rounded-xl bg-[var(--creamed-oat)] p-4">

                <h3 class="font-semibold text-[var(--ink)]">
                    Landmark
                </h3>

                <p class="mt-2 text-sm leading-6 text-[var(--muted)]">
                    We are located on the
                    <strong>2nd Floor of Alvarez Building</strong>,
                    directly above
                    <strong>FICOBank</strong>.
                    Look for the FICOBank branch along Burgos Street and head upstairs to reach Martinis & Manicures.
                </p>

            </div>

        </div>

    </div>
</section>

{{-- Booking --}}
<section id="book" class="section-band px-4 py-20 pb-28">
    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
        <aside class="warm-panel rounded-2xl p-6 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[.22em] text-[var(--desert-rock)]">Reservations</p>
            <h2 class="mt-4 text-3xl">Reserve Your Experience</h2>
            <p class="mt-4 text-sm leading-7 text-[var(--muted)]">
                We accept up to <strong>10 confirmed bookings</strong> per day so each client gets proper time and attention.
            </p>
            <div class="mt-6 space-y-3 text-sm text-[var(--muted)]">
                <p class="rounded-xl bg-[var(--feather-white)]/60 p-4">Choose your service and preferred schedule.</p>
                <p class="rounded-xl bg-[var(--feather-white)]/60 p-4">Some services may ask for a client signature before booking.</p>
                <p class="rounded-xl bg-[var(--feather-white)]/60 p-4">Your contact details are used only for appointment coordination and service records.</p>
            </div>
        </aside>

        <div class="glass rounded-2xl p-6 sm:p-8">

        <h2 class="text-2xl text-center mb-2">Appointment Request</h2>
        <p class="text-center text-[var(--muted)] text-sm mb-8">Fill out the details below and confirm before sending.</p>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-green-500/20 text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-red-500/20 text-red-700 text-sm">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>&bull; {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="bookingForm" method="POST" action="{{ route('appointments.store') }}" class="space-y-5" novalidate>
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Full name --}}
                <div>
                    <input type="text" id="field_name" name="full_name"
                           placeholder="Full Name"
                           value="{{ old('full_name') }}"
                           class="form-field w-full p-4 rounded-xl"
                           autocomplete="name">
                    <p id="hint_name" class="field-hint hidden"></p>
                </div>

                {{-- Contact --}}
                <div>
                    <input type="text" id="field_contact" name="contact_number"
                           placeholder="Contact Number (e.g. 09171234567)"
                           value="{{ old('contact_number') }}"
                           class="form-field w-full p-4 rounded-xl"
                           inputmode="tel" autocomplete="tel">
                    <p id="hint_contact" class="field-hint hidden"></p>
                </div>
            </div>

            {{-- Email --}}
            <div>
                <input type="email" id="field_email" name="email"
                       placeholder="Email Address (optional)"
                       value="{{ old('email') }}"
                       class="form-field w-full p-4 rounded-xl"
                       autocomplete="email">
                <p id="hint_email" class="field-hint hidden"></p>
            </div>

            {{-- Service --}}
            <div>
                <select id="field_service" name="service_id" class="form-field w-full p-4 rounded-xl">
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}"
                                data-requires-consent="{{ $service->requires_consent ? '1' : '0' }}"
                                {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
                <p id="hint_service" class="field-hint hidden"></p>
            </div>

            {{-- Date & Time --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <input type="date" id="field_date" name="date"
                           value="{{ old('date') }}"
                           min="{{ now()->toDateString() }}"
                           class="form-field w-full p-4 rounded-xl">
                    <p id="hint_date" class="field-hint hidden"></p>
                </div>

                <div>
                    <select id="field_time" name="time" class="form-field w-full p-4 rounded-xl">
                        <option value="">Select Time</option>
                        @foreach($onlineTimeSlots as $slot)
                            <option value="{{ $slot }}" {{ old('time') == $slot ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('g:i A') }}
                            </option>
                        @endforeach
                    </select>
                    <p id="hint_time" class="field-hint hidden"></p>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <textarea id="field_notes" name="notes"
                          placeholder="Special Requests (optional)"
                          rows="3"
                          class="form-field w-full p-4 rounded-xl resize-none">{{ old('notes') }}</textarea>
            </div>

            <x-consent-form
                section-id="consentSection"
                canvas-id="signatureCanvas"
                signature-input-id="consent_signature"
                accepted-input-id="consent_accepted"
                hint-id="hint_consent"
                clear-function="clearSignature"
            />

            <button type="button" onclick="openConfirmModal()"
                    class="w-full btn-gold py-3.5 rounded-xl tracking-wide transition-all duration-200 active:scale-[.98]">
                Submit Appointment Request
            </button>
        </form>
        </div>
    </div>
</section>

{{-- Gallery modal --}}
<div id="galleryModal"
     class="hidden fixed inset-0 z-[60] items-center justify-center bg-[#2c241f]/85 p-4 backdrop-blur-sm">
    <div class="modal-panel relative w-full max-w-5xl">
        <button type="button" onclick="closeGalleryModal()"
                class="absolute -top-12 right-0 rounded-full bg-[var(--feather-white)]
                       px-4 py-2 text-sm font-bold text-[var(--ink)] shadow-lg hover:bg-[var(--creamed-oat)] transition">
            ✕ Close
        </button>
        <img id="galleryModalImage" src="" alt=""
             class="max-h-[78vh] w-full rounded-2xl object-contain bg-[var(--feather-white)] shadow-2xl">
        <div id="galleryModalText" class="mt-3 hidden rounded-2xl bg-[var(--feather-white)]/90 p-4 text-[var(--ink)]">
            <h3 id="galleryModalTitle"   class="text-lg font-semibold"></h3>
            <p  id="galleryModalCaption" class="mt-1 text-sm text-[var(--muted)]"></p>
        </div>
    </div>
</div>

{{-- Confirm modal --}}
<div id="confirmModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#2c241f]/60 p-4">
    <div class="modal-panel glass rounded-2xl w-[90%] max-w-lg p-6 sm:p-8">
        <h2 class="text-2xl mb-5">Confirm Appointment</h2>
        <div class="space-y-2.5 text-sm text-[var(--muted)]">
            <p><strong class="text-[var(--ink)]">Name:</strong>    <span id="previewName"></span></p>
            <p><strong class="text-[var(--ink)]">Contact:</strong> <span id="previewContact"></span></p>
            <p><strong class="text-[var(--ink)]">Email:</strong>   <span id="previewEmail"></span></p>
            <p><strong class="text-[var(--ink)]">Service:</strong> <span id="previewService"></span></p>
            <p><strong class="text-[var(--ink)]">Date:</strong>    <span id="previewDate"></span></p>
            <p><strong class="text-[var(--ink)]">Time:</strong>    <span id="previewTime"></span></p>
            <p><strong class="text-[var(--ink)]">Notes:</strong>   <span id="previewNotes"></span></p>
        </div>
        <div class="flex justify-end gap-3 mt-7">
            <button onclick="closeConfirmModal()"
                    class="px-5 py-2.5 border border-[var(--desert-rock)]/40 rounded-xl
                           text-[var(--ink)] text-sm font-semibold hover:bg-[var(--creamed-oat)] transition">
                Cancel
            </button>
            <button onclick="confirmSubmit()"
                    class="btn-gold px-5 py-2.5 rounded-xl text-sm transition-all active:scale-[.97]">
                Confirm &amp; Book
            </button>
        </div>
    </div>
</div>

{{-- Fully booked modal --}}
<div id="fullModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#2c241f]/60 p-4">
    <div class="modal-panel glass rounded-2xl w-[90%] max-w-md p-7 sm:p-10 text-center">
        {{-- Icon --}}
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--creamed-oat)] text-sm font-bold text-[var(--desert-rock)]">
            FULL
        </div>
        <h2 class="text-2xl mb-3">We're Fully Booked</h2>
        <p class="text-[var(--muted)] text-sm leading-relaxed">
            Sorry, we've reached our limit of <strong>10 confirmed appointments</strong>
            for <strong id="fullModalDate"></strong>. <br><br>
            Please try again tomorrow, choose a different date, or feel free to
            <span class="text-[var(--desert-rock)] font-semibold">walk in</span> and
            we'll do our best to accommodate you.
        </p>
        <div class="mt-7 flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="closeFullModal(); scrollToDateField();"
                    class="btn-gold px-6 py-2.5 rounded-xl text-sm transition-all active:scale-[.97]">
                Try Another Date
            </button>
            <button onclick="closeFullModal()"
                    class="px-6 py-2.5 border border-[var(--desert-rock)]/40 rounded-xl
                           text-sm font-semibold hover:bg-[var(--creamed-oat)] transition">
                Dismiss
            </button>
        </div>
    </div>
</div>

<script>
/* Capacity check */
// Slot data injected from the controller (confirmed counts per date).
// Shape: { "2025-07-10": 8, "2025-07-11": 10 }
const bookedSlots = @json($bookedSlots ?? []);
const MAX_CAPACITY = 10;

function isFullyBooked(dateStr) {
    return (bookedSlots[dateStr] ?? 0) >= MAX_CAPACITY;
}

function updateAvailableTimes() {
    const timeField = document.getElementById('field_time');
    if (!timeField) return;

    Array.from(timeField.options).forEach((option) => {
        if (!option.value) return;

        const baseLabel = option.dataset.label || option.textContent.replace(' (Booked)', '').replace(' (Unavailable)', '');
        option.dataset.label = baseLabel;
        option.disabled = false;
        option.textContent = baseLabel;
    });
}

/* Date helpers */
function todayString() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function formatDateFriendly(dateStr) {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return `${months[parseInt(m,10)-1]} ${parseInt(d,10)}, ${y}`;
}

function formatTime(time) {
    if (!time) return '';
    let [h, m] = time.split(':');
    h = parseInt(h);
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m} ${ampm}`;
}

/* Set date min on load */
document.addEventListener('DOMContentLoaded', function () {
    const dateField = document.getElementById('field_date');
    if (dateField) {
        const today = todayString();
        dateField.min = today;

        // If old() value is in the past, clear it
        if (dateField.value && dateField.value < today) {
            dateField.value = '';
        }

        dateField.addEventListener('change', () => {
            updateAvailableTimes();
            validateDate();
            validateTime();
        });
    }

    updateAvailableTimes();
});

/* Field validation helpers */
function setHint(id, msg, type) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.className = `field-hint ${type}`;
    el.classList.toggle('hidden', !msg);
}

function setFieldState(fieldId, ok) {
    const el = document.getElementById(fieldId);
    if (!el) return;
    el.classList.toggle('field-error', !ok);
}

function validateName() {
    const val = document.getElementById('field_name').value.trim();
    if (!val) {
        setHint('hint_name', 'Full name is required.', 'error');
        setFieldState('field_name', false);
        return false;
    }
    if (val.length < 2) {
        setHint('hint_name', 'Please enter your full name.', 'error');
        setFieldState('field_name', false);
        return false;
    }
    setHint('hint_name', '', '');
    setFieldState('field_name', true);
    return true;
}

function validateContact() {
    const val = document.getElementById('field_contact').value.trim();
    const ph  = /^(\+63|0)9\d{9}$/.test(val.replace(/\s/g, ''));
    if (!val) {
        setHint('hint_contact', 'Contact number is required.', 'error');
        setFieldState('field_contact', false);
        return false;
    }
    if (!ph) {
        setHint('hint_contact', 'Enter a valid PH number (e.g. 09171234567).', 'error');
        setFieldState('field_contact', false);
        return false;
    }
    setHint('hint_contact', 'Looks good.', 'ok');
    setFieldState('field_contact', true);
    return true;
}

function validateEmail() {
    const val = document.getElementById('field_email').value.trim();
    if (!val) {
        setHint('hint_email', '', '');
        setFieldState('field_email', true);
        return true;
    }

    const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    if (!valid) {
        setHint('hint_email', 'Enter a valid email address or leave it blank.', 'error');
        setFieldState('field_email', false);
        return false;
    }

    setHint('hint_email', 'Email will be saved with this booking.', 'ok');
    setFieldState('field_email', true);
    return true;
}

function validateService() {
    const val = document.getElementById('field_service').value;
    if (!val) {
        setHint('hint_service', 'Please select a service.', 'error');
        setFieldState('field_service', false);
        return false;
    }
    setHint('hint_service', '', '');
    setFieldState('field_service', true);
    return true;
}

function selectedServiceRequiresConsent() {
    const select = document.getElementById('field_service');
    return select?.options[select.selectedIndex]?.dataset.requiresConsent === '1';
}

function toggleConsentSection() {
    const section = document.getElementById('consentSection');
    if (!section) return;

    section.classList.toggle('hidden', !selectedServiceRequiresConsent());
    if (!selectedServiceRequiresConsent()) {
        clearSignature();
        const accepted = document.getElementById('consent_accepted');
        if (accepted) accepted.checked = false;
        setHint('hint_consent', '', '');
    } else {
        resizeSignatureCanvas();
    }
}

function validateConsent() {
    if (!selectedServiceRequiresConsent()) return true;

    if (!document.getElementById('consent_accepted')?.checked) {
        setHint('hint_consent', 'Please read and accept the consent statements.', 'error');
        document.getElementById('consentSection')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    if (!document.getElementById('consent_signature').value) {
        setHint('hint_consent', 'Signature is required for this service.', 'error');
        document.getElementById('consentSection')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    setHint('hint_consent', 'Consent signed.', 'ok');
    return true;
}

function validateDate() {
    const dateField = document.getElementById('field_date');
    const val       = dateField.value;
    const today     = todayString();

    if (!val) {
        setHint('hint_date', 'Please choose a date.', 'error');
        setFieldState('field_date', false);
        return false;
    }
    if (val < today) {
        setHint('hint_date', 'Date cannot be in the past.', 'error');
        setFieldState('field_date', false);
        return false;
    }
    // Capacity check
    if (isFullyBooked(val)) {
        setHint('hint_date', `Fully booked on ${formatDateFriendly(val)}. Pick another date.`, 'error');
        setFieldState('field_date', false);
        return false;
    }
    setHint('hint_date', `${formatDateFriendly(val)} is available.`, 'ok');
    setFieldState('field_date', true);
    updateAvailableTimes();
    return true;
}

function validateTime() {
    const val = document.getElementById('field_time').value;
    const dateVal = document.getElementById('field_date').value;
    if (!val) {
        setHint('hint_time', 'Please select a time.', 'error');
        setFieldState('field_time', false);
        return false;
    }

    setHint('hint_time', '', '');
    setFieldState('field_time', true);
    return true;
}

/* Live validation on blur */
document.getElementById('field_name')?.addEventListener('blur', validateName);
document.getElementById('field_contact')?.addEventListener('blur', validateContact);
document.getElementById('field_email')?.addEventListener('blur', validateEmail);
document.getElementById('field_service')?.addEventListener('change', () => {
    validateService();
    toggleConsentSection();
    updateAvailableTimes();
    validateTime();
});
document.getElementById('field_time')?.addEventListener('change', validateTime);

/* Open confirm modal */
function openConfirmModal() {
    const okName    = validateName();
    const okContact = validateContact();
    const okEmail   = validateEmail();
    const okService = validateService();
    const okDate    = validateDate();
    const okTime    = validateTime();
    const okConsent = validateConsent();

    if (!okName || !okContact || !okEmail || !okService || !okDate || !okTime || !okConsent) {
        // Scroll to first error
        const firstErr = document.querySelector('.field-error');
        firstErr?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const dateVal = document.getElementById('field_date').value;

    // Final capacity check before opening confirm modal
    if (isFullyBooked(dateVal)) {
        openFullModal(dateVal);
        return;
    }

    // Populate preview
    const serviceSelect = document.getElementById('field_service');
    document.getElementById('previewName').textContent    = document.getElementById('field_name').value.trim();
    document.getElementById('previewContact').textContent = document.getElementById('field_contact').value.trim();
    document.getElementById('previewEmail').textContent   = document.getElementById('field_email').value.trim() || 'Not provided';
    document.getElementById('previewService').textContent = serviceSelect.options[serviceSelect.selectedIndex]?.text ?? '';
    document.getElementById('previewDate').textContent    = formatDateFriendly(dateVal);
    document.getElementById('previewTime').textContent    = formatTime(document.getElementById('field_time').value);
    document.getElementById('previewNotes').textContent   = document.getElementById('field_notes').value.trim() || 'None';

    document.getElementById('confirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

const signatureCanvas = document.getElementById('signatureCanvas');
const signatureInput = document.getElementById('consent_signature');
const signatureCtx = signatureCanvas?.getContext('2d');
let signatureDrawing = false;
let signatureHasInk = false;

function resizeSignatureCanvas() {
    if (!signatureCanvas || !signatureCtx) return;
    const data = signatureHasInk ? signatureCanvas.toDataURL('image/png') : null;
    const rect = signatureCanvas.getBoundingClientRect();
    signatureCanvas.width = Math.max(rect.width, 320);
    signatureCanvas.height = 160;
    signatureCtx.lineWidth = 2;
    signatureCtx.lineCap = 'round';
    signatureCtx.strokeStyle = '#4d4037';

    if (data) {
        const img = new Image();
        img.onload = () => signatureCtx.drawImage(img, 0, 0, signatureCanvas.width, signatureCanvas.height);
        img.src = data;
    }
}

function signaturePoint(event) {
    const rect = signatureCanvas.getBoundingClientRect();
    const source = event.touches?.[0] ?? event;
    return {
        x: source.clientX - rect.left,
        y: source.clientY - rect.top,
    };
}

function startSignature(event) {
    if (!signatureCanvas || !selectedServiceRequiresConsent()) return;
    event.preventDefault();
    signatureDrawing = true;
    const point = signaturePoint(event);
    signatureCtx.beginPath();
    signatureCtx.moveTo(point.x, point.y);
}

function drawSignature(event) {
    if (!signatureDrawing || !signatureCanvas) return;
    event.preventDefault();
    const point = signaturePoint(event);
    signatureCtx.lineTo(point.x, point.y);
    signatureCtx.stroke();
    signatureHasInk = true;
    signatureInput.value = signatureCanvas.toDataURL('image/png');
    setHint('hint_consent', 'Consent signed.', 'ok');
}

function stopSignature() {
    signatureDrawing = false;
}

function clearSignature() {
    if (!signatureCanvas || !signatureCtx) return;
    signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
    signatureHasInk = false;
    signatureInput.value = '';
}

signatureCanvas?.addEventListener('mousedown', startSignature);
signatureCanvas?.addEventListener('mousemove', drawSignature);
window.addEventListener('mouseup', stopSignature);
signatureCanvas?.addEventListener('touchstart', startSignature, { passive: false });
signatureCanvas?.addEventListener('touchmove', drawSignature, { passive: false });
window.addEventListener('touchend', stopSignature);
window.addEventListener('resize', resizeSignatureCanvas);
toggleConsentSection();

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function confirmSubmit() {
    closeConfirmModal();
    setTimeout(() => {
        document.getElementById('bookingForm').requestSubmit();
    }, 150);
}

/* Fully booked modal */
function openFullModal(dateStr) {
    document.getElementById('fullModalDate').textContent = formatDateFriendly(dateStr);
    document.getElementById('fullModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullModal() {
    document.getElementById('fullModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function scrollToDateField() {
    const f = document.getElementById('field_date');
    if (f) {
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => f.focus(), 400);
    }
}

/* Gallery modal */
function openGalleryModal(trigger) {
    const modal   = document.getElementById('galleryModal');
    const image   = document.getElementById('galleryModalImage');
    const title   = document.getElementById('galleryModalTitle');
    const caption = document.getElementById('galleryModalCaption');
    const text    = document.getElementById('galleryModalText');

    image.src           = trigger.dataset.gallerySrc;
    image.alt           = trigger.dataset.galleryTitle || 'Gallery image';
    title.textContent   = trigger.dataset.galleryTitle || '';
    caption.textContent = trigger.dataset.galleryCaption || '';
    text.classList.toggle('hidden', !title.textContent && !caption.textContent);

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
    const modal = document.getElementById('galleryModal');
    const image = document.getElementById('galleryModalImage');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    image.src = '';
    document.body.style.overflow = '';
}

document.getElementById('galleryModal')?.addEventListener('click', function (e) {
    if (e.target === this) closeGalleryModal();
});

/* Keyboard escape */
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (!document.getElementById('galleryModal')?.classList.contains('hidden')) closeGalleryModal();
    if (!document.getElementById('confirmModal')?.classList.contains('hidden')) closeConfirmModal();
    if (!document.getElementById('fullModal')?.classList.contains('hidden'))    closeFullModal();
});

/* Click outside to close confirm / full-booked modals */
['confirmModal','fullModal'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
});
</script>

</body>
</html>
