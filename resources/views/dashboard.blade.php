@extends('layouts.dashboard')

@section('header', 'Dashboard Overview')


@section('content')

@php
    $total     = $appointments->count();
    $pending   = $appointments->where('status', 'pending')->count();
    $confirmed = $appointments->where('status', 'confirmed')->count();
    $completed = $appointments->where('status', 'completed')->count();
    $rejected  = $appointments->where('status', 'rejected')->count();
@endphp

{{-- ===================== STAT CARDS ===================== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">

    {{-- Total --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 md:p-5 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Total</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $total }}</p>
        </div>
    </div>

    {{-- Pending --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 md:p-5 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Pending</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $pending }}</p>
        </div>
    </div>

    {{-- Confirmed --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 md:p-5 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Confirmed</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $confirmed }}</p>
        </div>
    </div>

    {{-- Completed --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-4 md:p-5 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-400 font-medium">Completed</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $completed }}</p>
        </div>
    </div>

</div>

{{-- ===================== RECENT APPOINTMENTS ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">

    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-800">Recent Appointments</h2>
        <a href="{{ route('appointments.index') }}"
           class="text-xs text-indigo-600 hover:text-indigo-700 font-medium transition">
            View all →
        </a>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3.5 text-left">Client</th>
                    <th class="px-5 py-3.5 text-left">Service</th>
                    <th class="px-5 py-3.5 text-left">Date & Time</th>
                    <th class="px-5 py-3.5 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($appointments->take(8) as $a)
            <tr class="hover:bg-gray-50/70 transition-colors">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-900">{{ $a->full_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $a->contact_number }}</p>
                </td>
                <td class="px-5 py-4 text-gray-700">{{ $a->service->name ?? '—' }}</td>
                <td class="px-5 py-4">
                    <p class="text-gray-800">{{ $a->date }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $a->time }}</p>
                </td>
                <td class="px-5 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        @if($a->status=='pending')   bg-amber-50  text-amber-700  ring-1 ring-amber-200
                        @elseif($a->status=='confirmed') bg-green-50  text-green-700  ring-1 ring-green-200
                        @elseif($a->status=='rejected')  bg-red-50    text-red-700    ring-1 ring-red-200
                        @elseif($a->status=='completed') bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200
                        @endif">
                        {{ ucfirst($a->status) }}
                    </span>
                    @if(($a->payment_status ?? null) === 'paid')
                    <span class="ml-2 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Paid
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-16 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-400 text-sm">No appointments yet.</p>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden divide-y divide-gray-50">
    @forelse($appointments->take(8) as $a)
    <div class="p-4">
        <div class="flex justify-between items-start gap-2 mb-2">
            <div>
                <p class="font-medium text-gray-900 text-sm">{{ $a->full_name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $a->contact_number }}</p>
            </div>
            <div class="flex flex-wrap gap-1.5 justify-end">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    @if($a->status=='pending')   bg-amber-50  text-amber-700  ring-1 ring-amber-200
                    @elseif($a->status=='confirmed') bg-green-50  text-green-700  ring-1 ring-green-200
                    @elseif($a->status=='rejected')  bg-red-50    text-red-700    ring-1 ring-red-200
                    @elseif($a->status=='completed') bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200
                    @endif">
                    {{ ucfirst($a->status) }}
                </span>
                @if(($a->payment_status ?? null) === 'paid')
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Paid
                </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs text-gray-400">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                {{ $a->service->name ?? '—' }}
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $a->date }}
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $a->time }}
            </span>
        </div>
    </div>
    @empty
    <div class="text-center py-16 text-gray-400 text-sm">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        No appointments yet.
    </div>
    @endforelse
    </div>

</div>

@endsection