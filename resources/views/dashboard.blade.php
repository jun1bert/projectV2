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
                <h2 class="text-sm font-bold text-[var(--ink)]">Recent Appointments</h2>
                <p class="mt-1 text-xs font-semibold text-[var(--muted)]">Latest booking activity and payment state</p>
            </div>
            <a href="{{ route('appointments.index') }}"
               class="text-sm font-bold text-[var(--desert-rock)] transition hover:text-[#8f7663]">
                View all
            </a>
        </div>

        <div class="hidden md:block">
            <table class="theme-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments->take(8) as $a)
                        <tr class="transition-colors hover:bg-[var(--porcelain-mist)]/70">
                            <td class="px-5 py-4">
                                <p class="font-bold text-[var(--ink)]">{{ $a->full_name }}</p>
                                <p class="mt-0.5 text-xs text-[var(--muted)]">{{ $a->contact_number }}</p>
                            </td>
                            <td class="px-5 py-4 text-[var(--muted)]">{{ $a->service->name ?? 'Unavailable' }}</td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-[var(--ink)]">{{ $a->date }}</p>
                                <p class="mt-0.5 text-xs text-[var(--muted)]">{{ $a->time }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeClass[$a->status] ?? 'bg-white text-[var(--muted)] ring-1 ring-[var(--soft-sandstone)]' }}">
                                    {{ ucfirst($a->status) }}
                                </span>
                                @if(($a->payment_status ?? null) === 'paid')
                                    <span class="ml-2 inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                        Paid
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center text-sm text-[var(--muted)]">
                                No appointments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[var(--soft-sandstone)]/30 md:hidden">
            @forelse($appointments->take(8) as $a)
                <article class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-bold text-[var(--ink)]">{{ $a->full_name }}</h3>
                            <p class="mt-0.5 text-xs text-[var(--muted)]">{{ $a->contact_number }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap justify-end gap-1.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeClass[$a->status] ?? 'bg-white text-[var(--muted)] ring-1 ring-[var(--soft-sandstone)]' }}">
                                {{ ucfirst($a->status) }}
                            </span>
                            @if(($a->payment_status ?? null) === 'paid')
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                    Paid
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 grid gap-1.5 text-xs font-semibold text-[var(--muted)]">
                        <span>{{ $a->service->name ?? 'Unavailable' }}</span>
                        <span>{{ $a->date }} at {{ $a->time }}</span>
                    </div>
                </article>
            @empty
                <div class="px-5 py-16 text-center text-sm text-[var(--muted)]">
                    No appointments yet.
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
