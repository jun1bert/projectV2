@extends('layouts.dashboard')

@section('header', 'Appointments')
@section('subheader', 'Manage bookings, assignments, and payments')

@section('content')
<title><?= config('app.name') ?> | Appointments</title>
@php
    $role = auth()->user()->role;
    $canManage = in_array($role, ['admin', 'management', 'staff', 'reception']);
    $canAssign = in_array($role, ['admin', 'management', 'reception']);
    $canEdit = in_array($role, ['admin', 'management', 'reception']);

    $statusStyles = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'completed' => 'bg-[var(--creamed-oat)] text-[var(--ink)] ring-[var(--soft-sandstone)]',
    ];
@endphp

@if(session('success') || session('error'))
<div id="pageToast"
     class="fixed top-4 right-4 z-[100] max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg
            {{ session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
    <div class="flex items-start gap-3">
        <span class="mt-1 h-2 w-2 rounded-full {{ session('success') ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
        <span>{{ session('success') ?? session('error') }}</span>
        <button type="button" class="ml-auto text-current/60 hover:text-current" onclick="this.closest('#pageToast').remove()">x</button>
    </div>
</div>
@endif

<style>
    .theme-card {
        background: rgba(250, 249, 246, .86);
        border: 1px solid rgba(164, 141, 120, .18);
        box-shadow: 0 18px 50px rgba(77, 64, 55, .08);
    }

    .theme-field {
        width: 100%;
        border-radius: 14px;
        border: 1px solid rgba(164, 141, 120, .25);
        background: rgba(250, 249, 246, .92);
        color: var(--ink);
        font-size: .875rem;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .theme-field:focus {
        border-color: var(--desert-rock);
        box-shadow: 0 0 0 4px rgba(164, 141, 120, .16);
        background: #fff;
    }

    input.theme-field[type="date"],
    input.theme-field[type="time"],
    select.theme-field {
        color: var(--ink);
        color-scheme: light;
        min-height: 42px;
        -webkit-text-fill-color: var(--ink);
        appearance: auto;
        -webkit-appearance: auto;
    }

    input.theme-field[type="date"]::-webkit-date-and-time-value,
    input.theme-field[type="time"]::-webkit-date-and-time-value {
        color: var(--ink);
        text-align: left;
    }

    .theme-button {
        background: var(--desert-rock);
        color: #fff;
    }

    .theme-button:hover {
        background: #927865;
    }

    .muted-button {
        border: 1px solid rgba(164, 141, 120, .25);
        color: var(--ink);
        background: rgba(250, 249, 246, .8);
    }

    .muted-button:hover {
        background: rgba(230, 218, 200, .48);
    }

    .soft-panel {
        background: linear-gradient(135deg, rgba(250,249,246,.94), rgba(244,241,234,.88));
        border: 1px solid rgba(164, 141, 120, .18);
    }

    .table-shell {
        background: rgba(250, 249, 246, .9);
        border: 1px solid rgba(164, 141, 120, .18);
        box-shadow: 0 18px 50px rgba(77, 64, 55, .08);
    }

    .staff-picker-hint { font-size: 10px; line-height: 1.25; color: var(--muted); }

    .staff-picker { position: relative; width: 168px; max-width: 100%; }
    .staff-picker-wide { width: 100%; }
    .staff-picker-trigger {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        text-align: left;
    }
    .staff-picker-trigger span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .staff-picker-menu {
        position: absolute;
        z-index: 70;
        top: calc(100% + .35rem);
        left: 0;
        width: min(260px, 80vw);
        overflow: hidden;
        border: 1px solid rgba(164, 141, 120, .24);
        border-radius: .8rem;
        background: var(--feather-white);
        box-shadow: 0 16px 38px rgba(77, 64, 55, .18);
    }
    .staff-picker-options { max-height: 180px; overflow-y: auto; padding: .4rem; }
    .staff-picker-option { display: flex; cursor: pointer; align-items: center; gap: .55rem; border-radius: .55rem; padding: .5rem .55rem; font-size: .75rem; }
    .staff-picker-option:hover { background: rgba(230, 218, 200, .45); }
    .staff-picker-footer { display: flex; justify-content: flex-end; border-top: 1px solid rgba(164, 141, 120, .18); padding: .45rem; }
    #editAppointmentModal > div > .theme-card { overflow: visible; }

    #desktopTable { display: none; }
    @media (min-width: 768px) {
        #desktopTable { display: block; }
        #mobileCards { display: none; }
    }

    .appointments-pagination nav > div:first-child {
        display: none;
    }

    .appointments-pagination nav > div:last-child {
        display: flex;
        justify-content: flex-end;
    }

    .appointments-pagination span[aria-current="page"] span {
        background: var(--desert-rock) !important;
        border-color: var(--desert-rock) !important;
        color: #fff !important;
    }

    .appointments-pagination a,
    .appointments-pagination span {
        border-color: rgba(164, 141, 120, .24) !important;
        color: var(--ink);
    }

    .appointments-pagination a:hover {
        background: rgba(230, 218, 200, .45) !important;
    }
</style>

<section class="space-y-5">
    <div class="soft-panel rounded-2xl p-4 md:p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[.18em] text-[var(--desert-rock)]">Appointment Desk</p>
                <div class="mt-2 flex flex-wrap items-end gap-x-4 gap-y-2">
                    <h2 class="text-2xl font-semibold text-[var(--ink)]">{{ $appointments->total() }} bookings</h2>
                    <p class="text-sm text-[var(--muted)]">
                        Page {{ $appointments->currentPage() }} of {{ $appointments->lastPage() }}
                    </p>
                </div>
            </div>

            <div class="grid gap-2 sm:grid-cols-[minmax(220px,1fr)_170px_auto] xl:min-w-[650px]">
                <input type="search"
                       id="globalSearch"
                       class="theme-field px-4 py-2.5"
                       placeholder="Search client, contact, email, service">

                <select id="statusFilter" class="theme-field px-4 py-2.5 sm:w-44">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>

                @if($canManage)
                <button type="button"
                        onclick="openWalkInModal()"
                        class="theme-button inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                    New walk-in
                </button>
                @endif
            </div>
        </div>
    </div>

    <div id="desktopTable" class="table-shell overflow-visible rounded-2xl">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[rgba(164,141,120,.18)] bg-[rgba(230,218,200,.32)] text-xs uppercase tracking-wide text-[var(--muted)]">
                    <th class="px-5 py-3.5 text-left font-semibold">Client</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Service</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Schedule</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                    @if($canManage)
                    <th class="px-5 py-3.5 text-left font-semibold">Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-[rgba(164,141,120,.14)]">
            @forelse($appointments as $appointment)
                <tr class="filter-row transition hover:bg-[rgba(230,218,200,.24)]"
                    data-search="{{ strtolower($appointment->full_name.' '.$appointment->contact_number.' '.($appointment->email ?? '').' '.$appointment->service_names.' '.$appointment->status.' '.($appointment->booking_type ?? 'online')) }}"
                    data-status="{{ $appointment->status }}"
                    data-price="{{ $appointment->services_total }}">
                    <td class="px-5 py-4 align-top">
                        <p class="font-semibold text-[var(--ink)]">{{ $appointment->full_name }}</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $appointment->contact_number }}</p>
                        @if($appointment->email)
                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $appointment->email }}</p>
                        @endif
                    </td>

                    <td class="px-5 py-4 align-top">
                        <p class="font-medium text-[var(--ink)]">{{ $appointment->service_names }}</p>
                        <p class="mt-1 text-xs text-[var(--desert-rock)]">PHP {{ number_format($appointment->services_total, 2) }}</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $appointment->party_size }} client{{ $appointment->party_size === 1 ? '' : 's' }}</p>
                        @if($appointment->party_size > 1)
                        <div class="mt-2 space-y-1 text-xs text-[var(--muted)]">
                            @foreach($appointment->participants as $participant)
                                <p><strong>{{ $participant->display_name }}:</strong> {{ $participant->services->pluck('name')->join(', ') }}</p>
                            @endforeach
                        </div>
                        @endif
                        @if($appointment->services->contains('requires_consent', true))
                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1
                            {{ $appointment->consentForm ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }}">
                            {{ $appointment->consentForm ? 'Consent signed' : 'Needs consent' }}
                        </span>
                        @endif
                    </td>

                    <td class="px-5 py-4 align-top">
                        <p class="font-medium text-[var(--ink)]">{{ $appointment->date }}</p>
                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $appointment->formatted_time }}</p>
                    </td>

                    <td class="px-5 py-4 align-top">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusStyles[$appointment->status] ?? 'bg-stone-50 text-stone-700 ring-stone-200' }}">
                                {{ ucfirst($appointment->status) }}
                            </span>

                            @if(($appointment->payment_status ?? null) === 'paid')
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Paid</span>
                            @elseif(($appointment->payment_status ?? null) === 'partially_paid')
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Partially paid</span>
                            @endif
                            @if($appointment->servicePackage)
                                <span class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">{{ $appointment->servicePackage->remaining_sessions }} session(s) left</span>
                            @endif
                        </div>
                        <p class="mt-2 max-w-44 text-xs leading-5 text-[var(--muted)]">
                            {{ $appointment->assigned_staff_names }}
                        </p>
                    </td>

                    @if($canManage)
                    <td class="px-5 py-4 align-top">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($canEdit)
                            <button type="button"
                                    class="preview-btn muted-button rounded-xl px-3 py-2 text-xs font-semibold transition"
                                    data-id="{{ $appointment->id }}">
                                Preview
                            </button>
                            <button type="button"
                                    class="edit-btn muted-button rounded-xl px-3 py-2 text-xs font-semibold transition"
                                    data-id="{{ $appointment->id }}">
                                Manage
                            </button>
                            @endif

                            @if($appointment->status === 'completed' && ($appointment->payment_status ?? null) !== 'paid')
                            <button type="button"
                                    class="pay-btn theme-button rounded-xl px-3 py-2 text-xs font-semibold transition"
                                    data-id="{{ $appointment->id }}">
                                Payment
                            </button>
                            @elseif($appointment->status === 'completed' && ($appointment->payment_status ?? null) === 'paid' && $appointment->billing_invoice)
                            <a href="{{ route('invoices.receipt', $appointment->billing_invoice->id) }}"
                               target="_blank"
                               rel="noopener"
                               class="muted-button rounded-xl px-3 py-2 text-xs font-semibold transition">
                                Receipt
                            </a>
                            @endif

                            @if($appointment->consentForm)
                            <button type="button"
                                    class="consent-btn muted-button rounded-xl px-3 py-2 text-xs font-semibold transition"
                                    data-id="{{ $appointment->id }}">
                                View Consent
                            </button>
                            @elseif($appointment->services->contains('requires_consent', true))
                            <button type="button" class="sign-consent-btn theme-button rounded-xl px-3 py-2 text-xs font-semibold" data-id="{{ $appointment->id }}" data-name="{{ $appointment->full_name }}">Sign Consent</button>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canManage ? 5 : 4 }}" class="px-5 py-16 text-center text-sm text-[var(--muted)]">
                        No appointments yet.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div id="mobileCards" class="grid gap-3">
        @forelse($appointments as $appointment)
        <article class="filter-row theme-card overflow-visible rounded-2xl"
                 data-search="{{ strtolower($appointment->full_name.' '.$appointment->contact_number.' '.($appointment->email ?? '').' '.$appointment->service_names.' '.$appointment->status.' '.($appointment->booking_type ?? 'online')) }}"
                 data-status="{{ $appointment->status }}"
                 data-price="{{ $appointment->services_total }}">
            <div class="border-b border-[rgba(164,141,120,.14)] bg-[rgba(230,218,200,.2)] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-semibold text-[var(--ink)]">{{ $appointment->full_name }}</h3>
                        <p class="mt-1 text-xs text-[var(--muted)]">{{ $appointment->contact_number }}</p>
                        @if($appointment->email)
                        <p class="mt-1 truncate text-xs text-[var(--muted)]">{{ $appointment->email }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusStyles[$appointment->status] ?? 'bg-stone-50 text-stone-700 ring-stone-200' }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                        @if(($appointment->payment_status ?? null) === 'paid')
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Paid</span>
                        @elseif(($appointment->payment_status ?? null) === 'partially_paid')
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Partially paid</span>
                        @endif
                        @if($appointment->servicePackage)
                            <span class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200">{{ $appointment->servicePackage->remaining_sessions }} session(s) left</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid gap-3 p-4 text-xs text-[var(--muted)]">
                <div class="grid grid-cols-[82px_1fr] gap-3">
                    <span>Service</span>
                    <span class="text-right font-medium text-[var(--ink)]">
                        {{ $appointment->service_names }}
                        @if($appointment->services->contains('requires_consent', true))
                        <span class="mt-1 block text-xs font-semibold {{ $appointment->consentForm ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $appointment->consentForm ? 'Consent signed' : 'Needs consent' }}
                        </span>
                        @endif
                        @if($appointment->party_size > 1)
                        <div class="mt-2 space-y-1 text-xs font-normal text-[var(--muted)]">
                            @foreach($appointment->participants as $participant)
                                <p><strong>{{ $participant->display_name }}:</strong> {{ $participant->services->pluck('name')->join(', ') }}</p>
                            @endforeach
                        </div>
                        @endif
                    </span>
                </div>
                <div class="grid grid-cols-[82px_1fr] gap-3">
                    <span>Amount</span>
                    <span class="text-right font-medium text-[var(--desert-rock)]">PHP {{ number_format($appointment->services_total, 2) }}</span>
                </div>
                <div class="grid grid-cols-[82px_1fr] gap-3">
                    <span>Schedule</span>
                    <span class="text-right font-medium text-[var(--ink)]">{{ $appointment->date }} at {{ $appointment->formatted_time }}</span>
                </div>
                <div class="grid grid-cols-[82px_1fr] gap-3">
                    <span>Staff</span>
                    <span class="text-right font-medium text-[var(--ink)]">{{ $appointment->assigned_staff_names }}</span>
                </div>
            </div>

            @if($canManage)
            <div class="grid gap-2 border-t border-[rgba(164,141,120,.16)] p-4 sm:grid-cols-2">
                @if($canEdit)
                <button type="button"
                        class="preview-btn muted-button rounded-xl px-3 py-2.5 text-xs font-semibold transition"
                        data-id="{{ $appointment->id }}">
                    Preview
                </button>
                <button type="button"
                        class="edit-btn muted-button rounded-xl px-3 py-2.5 text-xs font-semibold transition"
                        data-id="{{ $appointment->id }}">
                    Manage
                </button>
                @endif

                @if($appointment->status === 'completed' && ($appointment->payment_status ?? null) !== 'paid')
                <button type="button"
                        class="pay-btn theme-button rounded-xl px-3 py-2.5 text-xs font-semibold transition"
                        data-id="{{ $appointment->id }}">
                    Payment
                </button>
                @elseif($appointment->status === 'completed' && ($appointment->payment_status ?? null) === 'paid' && $appointment->billing_invoice)
                <a href="{{ route('invoices.receipt', $appointment->billing_invoice->id) }}"
                   target="_blank"
                   rel="noopener"
                   class="muted-button rounded-xl px-3 py-2.5 text-center text-xs font-semibold transition">
                    Receipt
                </a>
                @endif

                @if($appointment->consentForm)
                <button type="button"
                        class="consent-btn muted-button rounded-xl px-3 py-2.5 text-xs font-semibold transition"
                        data-id="{{ $appointment->id }}">
                    View Consent
                </button>
                @elseif($appointment->services->contains('requires_consent', true))
                <button type="button" class="sign-consent-btn theme-button rounded-xl px-3 py-2.5 text-xs font-semibold" data-id="{{ $appointment->id }}" data-name="{{ $appointment->full_name }}">Sign Consent</button>
                @endif
            </div>
            @endif
        </article>
        @empty
        <div class="theme-card rounded-2xl px-5 py-16 text-center text-sm text-[var(--muted)]">
            No appointments yet.
        </div>
        @endforelse
    </div>

    @if($appointments->hasPages())
    <div class="theme-card rounded-2xl px-4 py-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-[var(--muted)]">
                Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} appointments
            </p>

            <div class="appointments-pagination">
                {{ $appointments->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
    @endif
</section>

@if($canManage)
<div id="walkInModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeWalkIn()" aria-label="Close walk-in modal"></button>

        <div class="theme-card relative w-full max-w-lg overflow-visible rounded-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--ink)]">New Walk-in</h2>
                    <p class="text-xs text-[var(--muted)]">Create a same-day appointment from the desk.</p>
                </div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeWalkIn()">Close</button>
            </div>

            <form id="walkInForm" class="space-y-4 px-5 py-5">
                @csrf
                <input type="hidden" name="client_id" id="walkInClientId">

                <div class="relative">
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Full name</label>
                    <input type="text" id="walkInFullName" name="full_name" class="theme-field px-3 py-2.5" autocomplete="off" required>
                    <div id="walkInClientSuggestions" class="absolute z-50 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-[rgba(164,141,120,.24)] bg-[var(--feather-white)] p-1 shadow-xl"></div>
                    <p id="walkInClientHint" class="mt-1.5 text-xs text-[var(--muted)]">Type at least 2 characters to find a returning client.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Contact number</label>
                    <input type="text" id="walkInContactNumber" name="contact_number" class="theme-field px-3 py-2.5" placeholder="09XXXXXXXXX" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Email address <span class="font-normal">(optional)</span></label>
                    <input type="email" id="walkInEmail" name="email" class="theme-field px-3 py-2.5" placeholder="client@example.com">
                </div>

                <div id="walkInPackageSection" class="hidden">
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Existing package <span class="font-normal">(optional)</span></label>
                    <select id="walkInServicePackage" name="service_package_id" class="theme-field px-3 py-2.5">
                        <option value="">Start a new service/package</option>
                    </select>
                    <p class="mt-1.5 text-xs text-[var(--muted)]">Choose a package to use one of the client’s remaining sessions.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Service category</label>
                    <select id="walkInServiceCategory" class="theme-field px-3 py-2.5" required>
                        <option value="">Select a category</option>
                        @foreach(($services ?? collect())->pluck('category')->unique() as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Service</label>
                    <select id="walkInService" name="service_ids[]" multiple size="5" class="theme-field px-3 py-2.5" required>
                        @foreach($services ?? [] as $service)
                        <option value="{{ $service->id }}"
                                data-category="{{ $service->category }}"
                                data-requires-consent="{{ $service->requires_consent ? '1' : '0' }}"
                                data-duration="{{ $service->duration }}">
                            {{ $service->name }} — PHP {{ number_format($service->price, 2) }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-[var(--muted)]">Choose one or more services. Packages must be booked alone.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Number of clients</label>
                        <input type="number" name="party_size" min="1" max="50" value="1" class="theme-field px-3 py-2.5" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Date</label>
                        <input type="date" name="date" class="theme-field px-3 py-2.5" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Time</label>
                        <input type="time" name="time" class="theme-field px-3 py-2.5" required>
                    </div>
                </div>

                @if($canAssign)
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Assign staff <span class="font-normal">(optional)</span></label>
                    <select multiple size="4" id="walkInAssignedTo" name="assigned_staff_ids[]" class="theme-field max-h-32 px-3 py-2.5">
                        @foreach($staff ?? [] as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <p class="staff-picker-hint mt-1.5">Choose one or more staff members.</p>
                </div>
                @endif

                <x-consent-form
                    section-id="walkInConsentSection"
                    canvas-id="walkInSignatureCanvas"
                    signature-input-id="walkInConsentSignature"
                    accepted-input-id="walkInConsentAccepted"
                    hint-id="walkInConsentHint"
                    clear-function="clearWalkInSignature"
                    :compact="true"
                />

                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeWalkIn()">Cancel</button>
                    <button type="submit" id="walkInSubmitBtn" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Create appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div id="previewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closePreviewModal()" aria-label="Close preview modal"></button>

        <div class="theme-card relative max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--ink)]">Appointment Preview</h2>
                    <p id="previewModalMeta" class="mt-1 text-xs text-[var(--muted)]"></p>
                </div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closePreviewModal()">Close</button>
            </div>

            <div class="grid gap-3 px-5 py-5 sm:grid-cols-2">
                <div class="rounded-xl bg-[rgba(230,218,200,.28)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Client</p>
                    <p id="previewClient" class="mt-1 font-semibold text-[var(--ink)]"></p>
                    <p id="previewContact" class="mt-1 text-sm text-[var(--muted)]"></p>
                    <p id="previewEmail" class="mt-1 text-sm text-[var(--muted)]"></p>
                </div>
                <div class="rounded-xl bg-[rgba(230,218,200,.28)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Service</p>
                    <p id="previewService" class="mt-1 font-semibold text-[var(--ink)]"></p>
                    <p id="previewAmount" class="mt-1 text-sm text-[var(--desert-rock)]"></p>
                </div>
                <div class="rounded-xl bg-[rgba(230,218,200,.28)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Schedule</p>
                    <p id="previewSchedule" class="mt-1 font-semibold text-[var(--ink)]"></p>
                    <p id="previewBooking" class="mt-1 text-sm text-[var(--muted)]"></p>
                </div>
                <div class="rounded-xl bg-[rgba(230,218,200,.28)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Status</p>
                    <p id="previewStatus" class="mt-1 font-semibold capitalize text-[var(--ink)]"></p>
                    <p id="previewAssigned" class="mt-1 text-sm text-[var(--muted)]"></p>
                </div>
                <div class="rounded-xl bg-[rgba(230,218,200,.28)] p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Notes</p>
                    <p id="previewNotes" class="mt-1 text-sm leading-relaxed text-[var(--ink)]"></p>
                </div>
                <div class="rounded-xl border border-[rgba(164,141,120,.18)] bg-white p-4 sm:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Client Consent</p>
                        <span id="previewConsentStatus" class="rounded-full px-2.5 py-1 text-xs font-semibold"></span>
                    </div>
                    <p id="previewConsentMeta" class="mt-2 text-xs text-[var(--muted)]"></p>
                    <div id="previewConsentDetails" class="mt-4 hidden space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-[var(--muted)]">Recorded terms</p>
                            <p id="previewConsentText" class="mt-1 whitespace-pre-line rounded-xl bg-[rgba(230,218,200,.28)] p-3 text-sm leading-relaxed text-[var(--ink)]"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-[var(--muted)]">Client signature</p>
                            <div class="mt-1 rounded-xl border border-[rgba(164,141,120,.18)] bg-white p-3">
                                <img id="previewConsentSignature" src="" alt="Client consent signature" class="max-h-40 w-full object-contain">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canEdit)
<div id="editAppointmentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeEditAppointmentModal()" aria-label="Close edit appointment modal"></button>

        <div class="theme-card relative w-full max-w-2xl overflow-hidden rounded-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--ink)]">Edit Appointment</h2>
                    <p class="text-xs text-[var(--muted)]">Update client, service, schedule, status, and assignment.</p>
                </div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeEditAppointmentModal()">Close</button>
            </div>

            <form id="editAppointmentForm" class="space-y-4 px-5 py-5">
                @csrf
                <input type="hidden" name="appointment_id" id="edit_appointment_id">

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Full name</label>
                        <input id="edit_full_name" name="full_name" class="theme-field px-3 py-2.5" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Contact number</label>
                        <input id="edit_contact_number" name="contact_number" class="theme-field px-3 py-2.5" required>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Email address <span class="font-normal">(optional)</span></label>
                    <input id="edit_email" type="email" name="email" class="theme-field px-3 py-2.5" placeholder="client@example.com">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Number of clients</label>
                    <input id="edit_party_size" type="number" name="party_size" min="1" max="50" class="theme-field px-3 py-2.5" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Service</label>
                    <select id="edit_service_id" name="service_ids[]" multiple size="5" class="theme-field px-3 py-2.5" required>
                        @foreach($services ?? [] as $service)
                        <option value="{{ $service->id }}" data-duration="{{ $service->duration }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                    <p id="editServiceLockHint" class="mt-1 hidden text-xs text-amber-700">Service cannot be changed after payment.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Date</label>
                        <input id="edit_date" type="date" name="date" class="theme-field px-3 py-2.5" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Time</label>
                        <input id="edit_time" type="time" name="time" class="theme-field px-3 py-2.5" required>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Status</label>
                        <select id="edit_status" name="status" class="theme-field px-3 py-2.5" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Assign staff <span class="font-normal">(optional)</span></label>
                        <select multiple size="4" id="edit_assigned_staff_ids" name="assigned_staff_ids[]" class="theme-field max-h-32 px-3 py-2.5">
                            @foreach($staff ?? [] as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <p class="staff-picker-hint mt-1.5">Choose one or more staff members.</p>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Notes</label>
                    <textarea id="edit_notes" name="notes" rows="3" class="theme-field resize-none px-3 py-2.5"></textarea>
                </div>

                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeEditAppointmentModal()">Cancel</button>
                    <button type="submit" id="editAppointmentSubmitBtn" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div id="storeConsentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeStoreConsent()" aria-label="Close consent form"></button>
        <div class="theme-card relative w-full max-w-2xl rounded-2xl p-5">
            <div class="mb-4 flex items-start justify-between gap-3"><div><h2 class="text-lg font-semibold text-[var(--ink)]">In-store Client Consent</h2><p id="storeConsentClient" class="mt-1 text-xs text-[var(--muted)]"></p></div><button type="button" onclick="closeStoreConsent()" class="muted-button rounded-lg px-3 py-1.5 text-sm">Close</button></div>
            <form id="storeConsentForm">
                <input type="hidden" id="storeConsentAppointmentId">
                <x-consent-form section-id="storeConsentSection" canvas-id="storeConsentCanvas" signature-input-id="storeConsentSignature" accepted-input-id="storeConsentAccepted" hint-id="storeConsentHint" clear-function="clearStoreConsentSignature" compact />
                <button type="submit" class="theme-button mt-4 w-full rounded-xl px-4 py-3 text-sm font-semibold">Save signed consent</button>
            </form>
        </div>
    </div>
</div>

<div id="consentViewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeConsentView()" aria-label="Close consent modal"></button>

        <div class="theme-card relative w-full max-w-2xl overflow-hidden rounded-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--ink)]">Signed Consent</h2>
                    <p id="consentViewMeta" class="mt-1 text-xs text-[var(--muted)]"></p>
                </div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeConsentView()">Close</button>
            </div>

            <div class="space-y-4 px-5 py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Consent text</p>
                    <p id="consentViewText" class="mt-2 rounded-xl bg-[rgba(230,218,200,.3)] p-4 text-sm leading-relaxed text-[var(--ink)]"></p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">Signature</p>
                    <div class="mt-2 rounded-xl border border-[rgba(164,141,120,.18)] bg-white p-3">
                        <img id="consentViewSignature" src="" alt="Client signature" class="max-h-56 w-full object-contain">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="paymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closePayment()" aria-label="Close payment modal"></button>

        <div class="theme-card relative w-full max-w-lg overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--ink)]">Process Payment</h2>
                    <p class="text-xs text-[var(--muted)]">Accept a full payment or record an installment.</p>
                </div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closePayment()">Close</button>
            </div>

            <form id="paymentForm" class="space-y-4 px-5 py-5">
                @csrf
                <input type="hidden" name="appointment_id" id="payment_appointment_id">

                <div class="rounded-xl border border-[rgba(164,141,120,.18)] bg-[rgba(230,218,200,.32)] p-4 text-sm">
                    <div class="flex justify-between gap-4 text-[var(--muted)]">
                        <span>Service</span>
                        <span id="pm_service_price" class="font-semibold text-[var(--ink)]">PHP 0.00</span>
                    </div>
                    <div class="mt-3 flex justify-between gap-4 border-t border-[rgba(164,141,120,.18)] pt-3">
                        <span class="font-semibold text-[var(--ink)]">Total</span>
                        <span id="pm_total" class="font-bold text-[var(--desert-rock)]">PHP 0.00</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-4 text-[var(--muted)]">
                        <span>Already paid</span>
                        <span id="pm_paid" class="font-semibold text-[var(--ink)]">PHP 0.00</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-4 text-[var(--muted)]">
                        <span>Remaining balance</span>
                        <span id="pm_balance" class="font-semibold text-rose-700">PHP 0.00</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-4 text-[var(--muted)]">
                        <span>Clients</span>
                        <span id="pm_clients" class="font-semibold text-[var(--ink)]">1</span>
                    </div>
                    <div class="mt-2 flex justify-between gap-4 text-[var(--muted)]">
                        <span>Client services</span>
                        <span class="font-semibold text-[var(--ink)]">Priced individually</span>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Payment type</label>
                    <select name="payment_type" id="payment_type" class="theme-field px-3 py-2.5" required>
                        <option value="full">Full remaining balance</option>
                        <option value="per_client">Pay selected client(s)</option>
                        <option value="partial">Partial payment</option>
                    </select>
                </div>

                <div id="paymentClientCountField" class="hidden">
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Select client(s) paying now</label>
                    <input type="hidden" name="client_count" id="payment_client_count" value="1">
                    <div id="paymentParticipantChoices" class="space-y-2"></div>
                    <p id="paymentClientHint" class="mt-1 text-xs text-[var(--muted)]"></p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Amount to pay</label>
                    <input type="number" name="amount" id="payment_amount" min="0.01" step="0.01" class="theme-field px-3 py-2.5" readonly required>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Payment method</label>
                    <select name="method" id="payment_method" class="theme-field px-3 py-2.5" required>
                        <option value="">Select method</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Notes</label>
                    <textarea name="notes" rows="2" class="theme-field resize-none px-3 py-2.5" placeholder="Optional"></textarea>
                </div>

                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    <button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closePayment()">Cancel</button>
                    <button type="submit" id="paymentSubmitBtn" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold transition">Confirm payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const csrfToken = @json(csrf_token());
const paymentModal = document.getElementById('paymentModal');
const paymentForm = document.getElementById('paymentForm');
const walkInModal = document.getElementById('walkInModal');
const consentRecords = @json($consentRecords);
const appointmentRecords = @json($appointmentRecords);
let currentServicePrice = 0;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}

function enhanceStaffPicker(select) {
    if (!select || select.dataset.enhanced === 'true') return;
    select.dataset.enhanced = 'true';
    select.classList.add('hidden');

    const picker = document.createElement('div');
    picker.className = 'staff-picker staff-picker-wide';
    picker.innerHTML = `
        <button type="button" class="staff-picker-trigger theme-field px-3 py-2.5 text-xs" aria-expanded="false">
            <span>Select staff</span><b aria-hidden="true">⌄</b>
        </button>
        <div class="staff-picker-menu hidden">
            <div class="staff-picker-options"></div>
            <div class="staff-picker-footer">
                <button type="button" class="staff-picker-done theme-button rounded-lg px-3 py-1.5 text-xs font-semibold">Done</button>
            </div>
        </div>`;
    select.insertAdjacentElement('afterend', picker);

    const trigger = picker.querySelector('.staff-picker-trigger');
    const menu = picker.querySelector('.staff-picker-menu');
    const options = picker.querySelector('.staff-picker-options');

    function updateSummary() {
        const names = Array.from(select.selectedOptions).map((option) => option.textContent.trim());
        trigger.querySelector('span').textContent = names.length === 0
            ? 'Unassigned'
            : names.length <= 2 ? names.join(', ') : `${names.length} staff selected`;
        picker.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
            checkbox.checked = Array.from(select.selectedOptions).some((option) => option.value === checkbox.value);
        });
    }

    Array.from(select.options).forEach((option) => {
        const label = document.createElement('label');
        label.className = 'staff-picker-option';
        label.innerHTML = `<input type="checkbox" value="${escapeHtml(option.value)}" class="rounded border-[var(--soft-sandstone)]"> <span>${escapeHtml(option.textContent)}</span>`;
        label.querySelector('input').addEventListener('change', (event) => {
            option.selected = event.target.checked;
            updateSummary();
        });
        options.appendChild(label);
    });

    trigger.addEventListener('click', () => {
        const opening = menu.classList.contains('hidden');
        document.querySelectorAll('.staff-picker-menu').forEach((other) => other.classList.add('hidden'));
        menu.classList.toggle('hidden', !opening);
        trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
    });

    picker.querySelector('.staff-picker-done').addEventListener('click', () => {
        menu.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
    });

    select._updateStaffPicker = updateSummary;
    updateSummary();
}

document.querySelectorAll('select[multiple][name="assigned_staff_ids[]"]').forEach(enhanceStaffPicker);
document.addEventListener('click', (event) => {
    if (event.target.closest('.staff-picker')) return;
    document.querySelectorAll('.staff-picker-menu').forEach((menu) => menu.classList.add('hidden'));
});

function showToast(message, type = 'error') {
    document.getElementById('pageToast')?.remove();

    const toast = document.createElement('div');
    toast.id = 'pageToast';
    toast.className = `fixed top-4 right-4 z-[100] max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg ${
        type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
            : 'border-rose-200 bg-rose-50 text-rose-800'
    }`;
    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <span class="mt-1 h-2 w-2 rounded-full ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}"></span>
            <span>${escapeHtml(message)}</span>
            <button type="button" class="ml-auto text-current/60 hover:text-current" onclick="this.closest('#pageToast').remove()">x</button>
        </div>
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

function money(value) {
    return `PHP ${(Number(value) || 0).toFixed(2)}`;
}

function setBodyLock(isLocked) {
    document.body.style.overflow = isLocked ? 'hidden' : '';
}

function openWalkInModal() {
    walkInModal?.classList.remove('hidden');
    setBodyLock(true);
    resizeWalkInSignatureCanvas();
}

function closeWalkIn() {
    walkInModal?.classList.add('hidden');
    setBodyLock(false);
    clearWalkInSignature();
    closeClientSuggestions();
}

const walkInClientId = document.getElementById('walkInClientId');
const walkInFullName = document.getElementById('walkInFullName');
const walkInContactNumber = document.getElementById('walkInContactNumber');
const walkInEmail = document.getElementById('walkInEmail');
const walkInServiceCategory = document.getElementById('walkInServiceCategory');
const walkInService = document.getElementById('walkInService');
const walkInServicePackage = document.getElementById('walkInServicePackage');
const walkInPackageSection = document.getElementById('walkInPackageSection');
const walkInClientSuggestions = document.getElementById('walkInClientSuggestions');
const walkInClientHint = document.getElementById('walkInClientHint');
let clientSearchTimer;
let fillingClient = false;

function filterWalkInServices(category) {
    if (!walkInService) return;

    Array.from(walkInService.options).forEach((option) => {
        const visible = !category || option.dataset.category === category || option.selected;
        option.hidden = !visible;
        option.disabled = false;
    });

    walkInService.disabled = false;
}

filterWalkInServices('');
walkInServiceCategory?.addEventListener('change', () => {
    if (walkInServicePackage) walkInServicePackage.value = '';
    filterWalkInServices(walkInServiceCategory.value);
    toggleWalkInConsent();
});

walkInServicePackage?.addEventListener('change', () => {
    const option = walkInServicePackage.options[walkInServicePackage.selectedIndex];
    if (!option?.value) return;

    walkInServiceCategory.value = option.dataset.category;
    filterWalkInServices(option.dataset.category);
    walkInService.value = option.dataset.serviceId;
    walkInService.dispatchEvent(new Event('change', { bubbles: true }));
});

function closeClientSuggestions() {
    walkInClientSuggestions?.classList.add('hidden');
}

function selectExistingClient(client) {
    fillingClient = true;
    walkInClientId.value = client.id;
    walkInFullName.value = client.full_name;
    walkInContactNumber.value = client.contact_number;
    walkInEmail.value = client.email || '';
    populateClientPackages(client.service_packages || []);
    walkInClientHint.textContent = `Returning client selected · ${client.appointments_count} previous visit${client.appointments_count === 1 ? '' : 's'}`;
    walkInClientHint.className = 'mt-1.5 text-xs font-semibold text-emerald-700';
    closeClientSuggestions();
    fillingClient = false;
}

function populateClientPackages(packages) {
    if (!walkInServicePackage || !walkInPackageSection) return;

    walkInServicePackage.innerHTML = '<option value="">Start a new service/package</option>';
    packages.forEach((servicePackage) => {
        const option = document.createElement('option');
        option.value = servicePackage.id;
        option.dataset.serviceId = servicePackage.service_id;
        option.dataset.category = servicePackage.service?.category || '';
        option.textContent = `${servicePackage.service?.name || 'Package'} - ${servicePackage.available_sessions} session(s) available`;
        walkInServicePackage.appendChild(option);
    });
    walkInPackageSection.classList.toggle('hidden', packages.length === 0);
}

function useNewClient() {
    walkInClientId.value = '';
    populateClientPackages([]);
    walkInClientHint.textContent = 'New client — a client record will be created with these details.';
    walkInClientHint.className = 'mt-1.5 text-xs font-semibold text-[var(--desert-rock)]';
    closeClientSuggestions();
}

async function searchPreviousClients(search) {
    if (search.length < 2) {
        closeClientSuggestions();
        return;
    }

    try {
        const response = await fetch(`/clients/search?q=${encodeURIComponent(search)}`, {
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) return;
        const clients = await response.json();

        walkInClientSuggestions.innerHTML = '';
        clients.forEach((client) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'block w-full rounded-lg px-3 py-2.5 text-left transition hover:bg-[rgba(230,218,200,.45)]';
            button.innerHTML = `
                <span class="block text-sm font-semibold text-[var(--ink)]">${escapeHtml(client.full_name)}</span>
                <span class="mt-0.5 block text-xs text-[var(--muted)]">${escapeHtml(client.contact_number)}${client.email ? ` · ${escapeHtml(client.email)}` : ''} · ${client.appointments_count} visit${client.appointments_count === 1 ? '' : 's'}</span>`;
            button.addEventListener('click', () => selectExistingClient(client));
            walkInClientSuggestions.appendChild(button);
        });

        const newButton = document.createElement('button');
        newButton.type = 'button';
        newButton.className = 'mt-1 block w-full border-t border-[rgba(164,141,120,.18)] px-3 py-2.5 text-left text-xs font-semibold text-[var(--desert-rock)] hover:bg-[rgba(230,218,200,.35)]';
        newButton.textContent = `Create new client “${search}”`;
        newButton.addEventListener('click', useNewClient);
        walkInClientSuggestions.appendChild(newButton);
        walkInClientSuggestions.classList.remove('hidden');
    } catch (_) {
        closeClientSuggestions();
    }
}

[walkInFullName, walkInContactNumber, walkInEmail].forEach((input) => {
    input?.addEventListener('input', () => {
        if (fillingClient) return;
        if (walkInClientId.value) useNewClient();
        clearTimeout(clientSearchTimer);
        const search = input.value.trim();
        clientSearchTimer = setTimeout(() => searchPreviousClients(search), 300);
    });
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('#walkInClientSuggestions') && !event.target.closest('#walkInFullName') && !event.target.closest('#walkInContactNumber') && !event.target.closest('#walkInEmail')) {
        closeClientSuggestions();
    }
});

function walkInRequiresConsent() {
    const select = document.getElementById('walkInService');
    return Array.from(select?.selectedOptions || []).some(option => option.dataset.requiresConsent === '1');
}

function toggleWalkInConsent() {
    const section = document.getElementById('walkInConsentSection');
    if (!section) return;

    section.classList.toggle('hidden', !walkInRequiresConsent());
    if (walkInRequiresConsent()) {
        resizeWalkInSignatureCanvas();
        document.getElementById('walkInConsentHint').textContent = 'Signature is required for this service.';
    } else {
        clearWalkInSignature();
        const accepted = document.getElementById('walkInConsentAccepted');
        if (accepted) accepted.checked = false;
        document.getElementById('walkInConsentHint').textContent = '';
    }
}

function closePayment() {
    paymentModal.classList.add('hidden');
    setBodyLock(false);
    const button = document.getElementById('paymentSubmitBtn');
    if (button) {
        button.disabled = false;
        button.textContent = 'Confirm payment';
    }
}

function calculatePaymentTotal(totalValue = currentServicePrice, paidValue = 0, balanceValue = totalValue) {
    const total = Number(totalValue) || 0;
    const paid = Number(paidValue) || 0;
    const balance = Number(balanceValue) || 0;
    document.getElementById('pm_service_price').textContent = money(total);
    document.getElementById('pm_total').textContent = money(total);
    document.getElementById('pm_paid').textContent = money(paid);
    document.getElementById('pm_balance').textContent = money(balance);
    document.getElementById('payment_type').value = 'full';
    document.getElementById('payment_amount').readOnly = true;
    document.getElementById('payment_amount').value = balance.toFixed(2);
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('.pay-btn');
    if (!button) return;

    const appointment = appointmentRecords[button.dataset.id];
    if (!appointment) return;
    currentServicePrice = Number(appointment.billing_total || 0);
    document.getElementById('payment_appointment_id').value = appointment.id;
    calculatePaymentTotal(appointment.billing_total, appointment.billing_paid, appointment.billing_balance);
    document.getElementById('pm_clients').textContent = appointment.party_size || 1;
    const unpaidParticipants = (appointment.participants || []).filter(participant => Number(participant.paid) + 0.009 < Number(participant.total));
    document.getElementById('paymentParticipantChoices').innerHTML = unpaidParticipants.map(participant => `
        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[rgba(164,141,120,.2)] bg-white/60 p-3">
            <input type="checkbox" name="participant_ids[]" value="${participant.id}" data-balance="${Math.max(0, Number(participant.total) - Number(participant.paid))}" class="payment-participant mt-1">
            <span class="min-w-0 flex-1"><strong class="block text-sm text-[var(--ink)]">${escapeHtml(participant.name)}</strong><span class="block truncate text-xs text-[var(--muted)]">${escapeHtml(participant.services)}</span></span>
            <strong class="text-sm text-[var(--desert-rock)]">${money(Math.max(0, Number(participant.total) - Number(participant.paid)))}</strong>
        </label>`).join('');
    document.getElementById('payment_client_count').value = 1;
    document.getElementById('paymentClientHint').textContent = `${unpaidParticipants.length} client(s) with a remaining balance`;
    document.getElementById('paymentClientCountField').classList.add('hidden');
    paymentModal.classList.remove('hidden');
    setBodyLock(true);
});

document.getElementById('payment_type')?.addEventListener('change', (event) => {
    const amount = document.getElementById('payment_amount');
    const appointment = appointmentRecords[document.getElementById('payment_appointment_id').value];
    const balance = Number(appointment?.billing_balance || 0);
    const partial = event.target.value === 'partial';
    const perClient = event.target.value === 'per_client';
    document.getElementById('paymentClientCountField').classList.toggle('hidden', !perClient);
    amount.readOnly = !partial;
    amount.value = partial ? '' : (perClient ? '0.00' : balance.toFixed(2));
    if (partial) amount.focus();
});

document.getElementById('paymentParticipantChoices')?.addEventListener('change', () => {
    const selected = Array.from(document.querySelectorAll('.payment-participant:checked'));
    document.getElementById('payment_client_count').value = Math.max(1, selected.length);
    document.getElementById('payment_amount').value = selected.reduce((sum, input) => sum + Number(input.dataset.balance || 0), 0).toFixed(2);
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('.preview-btn');
    if (!button) return;

    openPreviewModal(button.dataset.id);
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('.edit-btn');
    if (!button) return;

    openEditAppointmentModal(button.dataset.id);
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('.sign-consent-btn');
    if (!button) return;
    document.getElementById('storeConsentAppointmentId').value = button.dataset.id;
    document.getElementById('storeConsentClient').textContent = `${button.dataset.name} — review and sign before treatment`;
    document.getElementById('storeConsentSection').classList.remove('hidden');
    document.getElementById('storeConsentModal').classList.remove('hidden');
    setBodyLock(true);
    setTimeout(resizeStoreConsentCanvas, 50);
});

const storeConsentCanvas = document.getElementById('storeConsentCanvas');
const storeConsentContext = storeConsentCanvas?.getContext('2d');
let storeConsentDrawing = false;

function resizeStoreConsentCanvas() {
    if (!storeConsentCanvas || !storeConsentContext) return;
    const width = Math.max(storeConsentCanvas.getBoundingClientRect().width, 320);
    storeConsentCanvas.width = width; storeConsentCanvas.height = 144;
    storeConsentContext.lineWidth = 2; storeConsentContext.lineCap = 'round'; storeConsentContext.strokeStyle = '#4d4037';
}
function storeConsentPoint(event) { const rect = storeConsentCanvas.getBoundingClientRect(); const source = event.touches?.[0] ?? event; return { x: source.clientX - rect.left, y: source.clientY - rect.top }; }
function startStoreConsent(event) { event.preventDefault(); storeConsentDrawing = true; const point = storeConsentPoint(event); storeConsentContext.beginPath(); storeConsentContext.moveTo(point.x, point.y); }
function drawStoreConsent(event) { if (!storeConsentDrawing) return; event.preventDefault(); const point = storeConsentPoint(event); storeConsentContext.lineTo(point.x, point.y); storeConsentContext.stroke(); document.getElementById('storeConsentSignature').value = storeConsentCanvas.toDataURL('image/png'); }
function stopStoreConsent() { storeConsentDrawing = false; }
function clearStoreConsentSignature() { storeConsentContext?.clearRect(0, 0, storeConsentCanvas.width, storeConsentCanvas.height); document.getElementById('storeConsentSignature').value = ''; }
function closeStoreConsent() { document.getElementById('storeConsentModal').classList.add('hidden'); setBodyLock(false); clearStoreConsentSignature(); }
storeConsentCanvas?.addEventListener('mousedown', startStoreConsent); storeConsentCanvas?.addEventListener('mousemove', drawStoreConsent); window.addEventListener('mouseup', stopStoreConsent);
storeConsentCanvas?.addEventListener('touchstart', startStoreConsent, { passive: false }); storeConsentCanvas?.addEventListener('touchmove', drawStoreConsent, { passive: false }); window.addEventListener('touchend', stopStoreConsent);

document.getElementById('storeConsentForm')?.addEventListener('submit', async event => {
    event.preventDefault();
    const accepted = document.getElementById('storeConsentAccepted')?.checked;
    const signature = document.getElementById('storeConsentSignature').value;
    if (!accepted || !signature) { document.getElementById('storeConsentHint').textContent = 'Acceptance and signature are required.'; return; }
    const id = document.getElementById('storeConsentAppointmentId').value;
    const response = await fetch(`/appointments/${id}/consent`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ consent_accepted: true, consent_signature: signature }) });
    const data = await response.json();
    if (!response.ok || !data.success) { document.getElementById('storeConsentHint').textContent = data.message || 'Unable to save consent.'; return; }
    window.location.reload();
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('.consent-btn');
    if (!button) return;

    const record = consentRecords[button.dataset.id];
    if (!record) return;

    const consentEmail = record.email ? ` - ${record.email}` : '';
    document.getElementById('consentViewMeta').textContent = `${record.name} - ${record.contact}${consentEmail} - ${record.service} - Signed ${record.signed_at}`;
    document.getElementById('consentViewText').textContent = record.consent_text;
    document.getElementById('consentViewSignature').src = record.signature_url;
    document.getElementById('consentViewModal').classList.remove('hidden');
    setBodyLock(true);
});

function closeConsentView() {
    document.getElementById('consentViewModal').classList.add('hidden');
    document.getElementById('consentViewSignature').src = '';
    setBodyLock(false);
}

function openPreviewModal(id) {
    const appointment = appointmentRecords[id];
    if (!appointment) return;

    document.getElementById('previewModalMeta').textContent = `#${appointment.id} - ${appointment.booking_type}`;
    document.getElementById('previewClient').textContent = appointment.full_name;
    document.getElementById('previewContact').textContent = appointment.contact_number;
    document.getElementById('previewEmail').textContent = appointment.email || 'Email not provided';
    document.getElementById('previewService').textContent = appointment.service_name;
    document.getElementById('previewAmount').textContent = money(appointment.service_price);
    document.getElementById('previewSchedule').textContent = `${appointment.date} at ${appointment.display_time}`;
    document.getElementById('previewBooking').textContent = `Booking: ${appointment.booking_type}`;
    document.getElementById('previewStatus').textContent = appointment.status;
    document.getElementById('previewAssigned').textContent = `Staff: ${appointment.assigned_staff}`;
    document.getElementById('previewNotes').textContent = appointment.notes || 'No notes.';

    const consent = consentRecords[id];
    const consentStatus = document.getElementById('previewConsentStatus');
    const consentDetails = document.getElementById('previewConsentDetails');
    const consentSignature = document.getElementById('previewConsentSignature');
    consentDetails.classList.toggle('hidden', !consent);

    if (consent) {
        consentStatus.textContent = 'Signed';
        consentStatus.className = 'rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200';
        document.getElementById('previewConsentMeta').textContent = `Signed ${consent.signed_at} for ${consent.service}`;
        document.getElementById('previewConsentText').textContent = consent.consent_text;
        consentSignature.src = consent.signature_url;
    } else {
        consentStatus.textContent = appointment.requires_consent ? 'Missing' : 'Not required';
        consentStatus.className = appointment.requires_consent
            ? 'rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200'
            : 'rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-600';
        document.getElementById('previewConsentMeta').textContent = appointment.requires_consent
            ? 'This service requires consent, but no signed record is attached.'
            : 'Consent is not required for this service.';
        document.getElementById('previewConsentText').textContent = '';
        consentSignature.src = '';
    }

    document.getElementById('previewModal').classList.remove('hidden');
    setBodyLock(true);
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    document.getElementById('previewConsentSignature').src = '';
    setBodyLock(false);
}

function openEditAppointmentModal(id) {
    const appointment = appointmentRecords[id];
    const modal = document.getElementById('editAppointmentModal');
    if (!appointment || !modal) return;

    document.getElementById('edit_appointment_id').value = appointment.id;
    document.getElementById('edit_full_name').value = appointment.full_name;
    document.getElementById('edit_contact_number').value = appointment.contact_number;
    document.getElementById('edit_email').value = appointment.email || '';
    document.getElementById('edit_party_size').value = appointment.party_size || 1;
    Array.from(document.getElementById('edit_service_id').options).forEach(option => {
        option.selected = (appointment.service_ids || [appointment.service_id]).map(String).includes(option.value);
    });
    document.getElementById('edit_date').value = appointment.date;
    document.getElementById('edit_time').value = appointment.time;
    document.getElementById('edit_status').value = appointment.status;
    document.getElementById('edit_notes').value = appointment.notes || '';
    const assignedIds = (appointment.assigned_staff_ids || []).map(String);
    Array.from(document.getElementById('edit_assigned_staff_ids').options).forEach((option) => {
        option.selected = assignedIds.includes(option.value);
    });
    document.getElementById('edit_assigned_staff_ids')._updateStaffPicker?.();

    const serviceSelect = document.getElementById('edit_service_id');
    const lockHint = document.getElementById('editServiceLockHint');
    serviceSelect.disabled = appointment.has_invoice;
    lockHint.classList.toggle('hidden', !appointment.has_invoice);

    modal.classList.remove('hidden');
    setBodyLock(true);
}

function closeEditAppointmentModal() {
    document.getElementById('editAppointmentModal')?.classList.add('hidden');
    setBodyLock(false);
}

document.getElementById('editAppointmentForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const id = document.getElementById('edit_appointment_id').value;
    const button = document.getElementById('editAppointmentSubmitBtn');
    const formData = new FormData(this);

    if (document.getElementById('edit_service_id').disabled) {
        formData.delete('service_ids[]');
        (appointmentRecords[id].service_ids || [appointmentRecords[id].service_id]).forEach(serviceId => formData.append('service_ids[]', serviceId));
    }

    button.disabled = true;
    button.textContent = 'Saving...';

    try {
        const response = await fetch(`/appointments/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: (() => {
                formData.append('_method', 'PUT');
                return formData;
            })()
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to update appointment.');
        }

        window.location.reload();
    } catch (error) {
        showToast(error.message || 'Something went wrong while updating the appointment.');
        button.disabled = false;
        button.textContent = 'Save changes';
    }
});

paymentForm?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const button = document.getElementById('paymentSubmitBtn');
    const appointmentId = document.getElementById('payment_appointment_id').value;
    const receiptWindow = window.open('', '_blank');
    if (receiptWindow) {
        receiptWindow.document.write('<!doctype html><title>Opening receipt...</title><body style="font-family:Arial,sans-serif;padding:24px;color:#4d4037;">Opening receipt...</body>');
        receiptWindow.document.close();
    }
    button.disabled = true;
    button.textContent = 'Processing...';

    try {
        const response = await fetch(`/appointments/${appointmentId}/payment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: new FormData(paymentForm)
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Payment failed.');
        }

        if (receiptWindow) {
            receiptWindow.location.href = `/invoices/${data.invoice_id}/receipt`;
        } else {
            window.location.href = `/invoices/${data.invoice_id}/receipt`;
        }
        window.location.reload();
    } catch (error) {
        receiptWindow?.close();
        showToast(error.message || 'Something went wrong while processing the payment.');
        button.disabled = false;
        button.textContent = 'Confirm payment';
    }
});

function applyFilters() {
    const search = (document.getElementById('globalSearch')?.value || '').trim().toLowerCase();
    const status = document.getElementById('statusFilter')?.value || '';

    document.querySelectorAll('.filter-row').forEach((row) => {
        const matchesSearch = !search || row.dataset.search.includes(search);
        const matchesStatus = !status || row.dataset.status === status;
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}

document.getElementById('globalSearch')?.addEventListener('input', applyFilters);
document.getElementById('statusFilter')?.addEventListener('change', applyFilters);

document.getElementById('walkInForm')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    if (walkInRequiresConsent() && !document.getElementById('walkInConsentAccepted')?.checked) {
        document.getElementById('walkInConsentHint').textContent = 'Please ask the client to read and accept the consent statements.';
        document.getElementById('walkInConsentSection')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (walkInRequiresConsent() && !document.getElementById('walkInConsentSignature').value) {
        document.getElementById('walkInConsentHint').textContent = 'Please ask the client to sign before creating the appointment.';
        document.getElementById('walkInConsentSection')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const button = document.getElementById('walkInSubmitBtn');
    button.disabled = true;
    button.textContent = 'Creating...';

    try {
        const response = await fetch('{{ route("appointments.walkin.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: new FormData(this)
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to create appointment.');
        }

        closeWalkIn();
        window.location.reload();
    } catch (error) {
        showToast(error.message || 'Something went wrong while creating the appointment.');
        button.disabled = false;
        button.textContent = 'Create appointment';
    }
});

const walkInSignatureCanvas = document.getElementById('walkInSignatureCanvas');
const walkInSignatureInput = document.getElementById('walkInConsentSignature');
const walkInSignatureCtx = walkInSignatureCanvas?.getContext('2d');
let walkInDrawing = false;
let walkInHasInk = false;

function resizeWalkInSignatureCanvas() {
    if (!walkInSignatureCanvas || !walkInSignatureCtx) return;
    const data = walkInHasInk ? walkInSignatureCanvas.toDataURL('image/png') : null;
    const rect = walkInSignatureCanvas.getBoundingClientRect();
    walkInSignatureCanvas.width = Math.max(rect.width, 320);
    walkInSignatureCanvas.height = 144;
    walkInSignatureCtx.lineWidth = 2;
    walkInSignatureCtx.lineCap = 'round';
    walkInSignatureCtx.strokeStyle = '#4d4037';

    if (data) {
        const img = new Image();
        img.onload = () => walkInSignatureCtx.drawImage(img, 0, 0, walkInSignatureCanvas.width, walkInSignatureCanvas.height);
        img.src = data;
    }
}

function walkInSignaturePoint(event) {
    const rect = walkInSignatureCanvas.getBoundingClientRect();
    const source = event.touches?.[0] ?? event;
    return {
        x: source.clientX - rect.left,
        y: source.clientY - rect.top,
    };
}

function startWalkInSignature(event) {
    if (!walkInSignatureCanvas || !walkInRequiresConsent()) return;
    event.preventDefault();
    walkInDrawing = true;
    const point = walkInSignaturePoint(event);
    walkInSignatureCtx.beginPath();
    walkInSignatureCtx.moveTo(point.x, point.y);
}

function drawWalkInSignature(event) {
    if (!walkInDrawing || !walkInSignatureCanvas) return;
    event.preventDefault();
    const point = walkInSignaturePoint(event);
    walkInSignatureCtx.lineTo(point.x, point.y);
    walkInSignatureCtx.stroke();
    walkInHasInk = true;
    walkInSignatureInput.value = walkInSignatureCanvas.toDataURL('image/png');
    document.getElementById('walkInConsentHint').textContent = 'Consent signed.';
}

function stopWalkInSignature() {
    walkInDrawing = false;
}

function clearWalkInSignature() {
    if (!walkInSignatureCanvas || !walkInSignatureCtx) return;
    walkInSignatureCtx.clearRect(0, 0, walkInSignatureCanvas.width, walkInSignatureCanvas.height);
    walkInHasInk = false;
    walkInSignatureInput.value = '';
}

document.getElementById('walkInService')?.addEventListener('change', () => {
    const packageOption = walkInServicePackage?.options[walkInServicePackage.selectedIndex];
    const selectedServiceIds = Array.from(walkInService.selectedOptions).map(option => option.value);
    if (packageOption?.value && (selectedServiceIds.length !== 1 || packageOption.dataset.serviceId !== selectedServiceIds[0])) {
        walkInServicePackage.value = '';
    }
    toggleWalkInConsent();
});
walkInSignatureCanvas?.addEventListener('mousedown', startWalkInSignature);
walkInSignatureCanvas?.addEventListener('mousemove', drawWalkInSignature);
window.addEventListener('mouseup', stopWalkInSignature);
walkInSignatureCanvas?.addEventListener('touchstart', startWalkInSignature, { passive: false });
walkInSignatureCanvas?.addEventListener('touchmove', drawWalkInSignature, { passive: false });
window.addEventListener('touchend', stopWalkInSignature);
window.addEventListener('resize', resizeWalkInSignatureCanvas);

setTimeout(() => document.getElementById('pageToast')?.remove(), 4000);
</script>

@endsection
