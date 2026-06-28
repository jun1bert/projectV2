@extends('layouts.dashboard')

@section('header', 'Dashboard Overview')
@section('subheader', 'Today at a glance for Martinis & Manicures')
<title><?= config('app.name') ?> | Dashboard Overview</title>
@section('content')
@php
    $total = $appointments->count();
    $pending = $appointments->where('status', 'pending')->count();
    $confirmed = $appointments->where('status', 'confirmed')->count();
    $completed = $appointments->where('status', 'completed')->count();
    $cancelled = $appointments->where('status', 'cancelled')->count();

    $stats = [
        ['label' => 'Total', 'value' => $total, 'tone' => 'bg-[var(--creamed-oat)] text-[var(--desert-rock)]'],
        ['label' => 'Pending', 'value' => $pending, 'tone' => 'bg-amber-50 text-amber-700'],
        ['label' => 'Confirmed', 'value' => $confirmed, 'tone' => 'bg-green-50 text-green-700'],
        ['label' => 'Completed', 'value' => $completed, 'tone' => 'bg-[var(--porcelain-mist)] text-[var(--ink)]'],
    ];

    $badgeClass = [
        'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        'confirmed' => 'bg-green-50 text-green-700 ring-1 ring-green-200',
        'cancelled' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
        'completed' => 'bg-[var(--creamed-oat)] text-[var(--desert-rock)] ring-1 ring-[var(--desert-rock)]/25',
    ];

    $calendarStart = \Carbon\Carbon::now()->startOfMonth();
    $calendarEnd = \Carbon\Carbon::now()->endOfMonth();
    $gridStart = $calendarStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $gridEnd = $calendarEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
    $calendarDays = collect();
    $cursor = $gridStart->copy();

    while ($cursor->lte($gridEnd)) {
        $calendarDays->push($cursor->copy());
        $cursor->addDay();
    }

    $appointmentsByDate = $appointments->groupBy(fn ($appointment) => \Carbon\Carbon::parse($appointment->date)->toDateString());
    $todayAppointments = $appointmentsByDate[\Carbon\Carbon::now()->toDateString()] ?? collect();
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach($stats as $stat)
            <section class="theme-card rounded-2xl p-4 md:p-5">
                <div class="flex items-center gap-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $stat['tone'] }}">
                        <span class="text-sm font-bold">{{ substr($stat['label'], 0, 1) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-[var(--ink)]">{{ $stat['value'] }}</p>
                    </div>
                </div>
            </section>
        @endforeach
    </div>

    <section class="theme-card overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-2 border-b border-[var(--soft-sandstone)]/35 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-[var(--ink)]">{{ $calendarStart->format('F Y') }} Appointment Calendar</h2>
                <p class="mt-1 text-xs font-semibold text-[var(--muted)]">Confirmed, pending, completed, and cancelled clients by date</p>
            </div>
            <a href="{{ route('appointments.index') }}"
               class="text-sm font-bold text-[var(--desert-rock)] transition hover:text-[#8f7663]">
                View all
            </a>
        </div>

        <div class="grid grid-cols-7 border-b border-[var(--soft-sandstone)]/30 bg-[var(--creamed-oat)]/35 text-center text-[11px] font-bold uppercase tracking-wide text-[var(--muted)]">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                <div class="px-2 py-3">{{ $dayName }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-7">
            @foreach($calendarDays as $day)
                @php
                    $dateKey = $day->toDateString();
                    $dayAppointments = ($appointmentsByDate[$dateKey] ?? collect())->sortBy('time');
                    $isCurrentMonth = $day->month === $calendarStart->month;
                    $isToday = $day->isToday();
                @endphp

                <article class="min-h-32 border-b border-r border-[var(--soft-sandstone)]/25 p-3 {{ $isCurrentMonth ? 'bg-[var(--feather-white)]/55' : 'bg-[var(--porcelain-mist)]/45 text-[var(--muted)]' }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="grid h-7 w-7 place-items-center rounded-full text-xs font-bold {{ $isToday ? 'bg-[var(--desert-rock)] text-white' : 'text-[var(--ink)]' }}">
                            {{ $day->day }}
                        </span>
                        @if($dayAppointments->isNotEmpty())
                            <span class="rounded-full bg-[var(--creamed-oat)] px-2 py-0.5 text-[10px] font-bold text-[var(--desert-rock)]">
                                {{ $dayAppointments->count() }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 space-y-2">
                        @forelse($dayAppointments->take(3) as $appointment)
                            <a href="{{ route('appointments.index') }}"
                               class="block rounded-xl border border-[var(--soft-sandstone)]/35 bg-white/65 px-2.5 py-2 transition hover:bg-[var(--creamed-oat)]/40">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-[11px] font-bold text-[var(--ink)]">{{ $appointment->full_name }}</span>
                                    <span class="shrink-0 text-[10px] font-bold text-[var(--desert-rock)]">{{ $appointment->formatted_time }}</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <span class="truncate text-[10px] font-semibold text-[var(--muted)]">{{ $appointment->service->name ?? 'Service' }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold {{ $badgeClass[$appointment->status] ?? 'bg-white text-[var(--muted)] ring-1 ring-[var(--soft-sandstone)]' }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="hidden text-[11px] font-semibold text-[var(--muted)] sm:block">No clients</p>
                        @endforelse

                        @if($dayAppointments->count() > 3)
                            <p class="text-[11px] font-bold text-[var(--desert-rock)]">+{{ $dayAppointments->count() - 3 }} more</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="theme-card rounded-2xl p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-[var(--ink)]">Today</h2>
                <p class="mt-1 text-xs font-semibold text-[var(--muted)]">{{ now()->format('F d, Y') }}</p>
            </div>
            <span class="rounded-full bg-[var(--creamed-oat)] px-3 py-1 text-xs font-bold text-[var(--desert-rock)]">
                {{ $todayAppointments->count() }} appointment{{ $todayAppointments->count() === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse($todayAppointments->sortBy('time') as $appointment)
                <article class="rounded-2xl border border-[var(--soft-sandstone)]/35 bg-[var(--feather-white)]/70 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-[var(--ink)]">{{ $appointment->full_name }}</p>
                            <p class="mt-1 text-xs font-semibold text-[var(--muted)]">{{ $appointment->service->name ?? 'Service' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeClass[$appointment->status] ?? 'bg-white text-[var(--muted)] ring-1 ring-[var(--soft-sandstone)]' }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs font-semibold text-[var(--muted)]">
                        <span>{{ $appointment->formatted_time }}</span>
                        <span>{{ $appointment->assigned_staff_names }}</span>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-[var(--soft-sandstone)]/60 px-5 py-10 text-center text-sm font-semibold text-[var(--muted)] md:col-span-2 xl:col-span-3">
                    No appointments scheduled today.
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
