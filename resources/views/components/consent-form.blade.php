@props([
    'sectionId',
    'canvasId',
    'signatureInputId',
    'acceptedInputId',
    'hintId',
    'clearFunction',
    'compact' => false,
])

@php($termsModalId = $sectionId . 'TermsModal')

<div id="{{ $sectionId }}" {{ $attributes->class([
    'hidden rounded-2xl border border-[rgba(164,141,120,.22)] bg-[rgba(250,249,246,.8)] p-4',
]) }}>
    <h3 class="{{ $compact ? 'text-sm' : 'text-lg' }} font-semibold text-[var(--ink)]">Client Consent</h3>
    <p class="mt-2 {{ $compact ? 'text-xs' : 'text-sm' }} leading-relaxed text-[var(--muted)]">
        Review the consent terms before accepting and signing for the selected service.
    </p>

    <button type="button"
            onclick="document.getElementById('{{ $termsModalId }}').showModal()"
            class="mt-3 inline-flex items-center rounded-xl border border-[rgba(164,141,120,.28)] bg-white px-3 py-2 {{ $compact ? 'text-xs' : 'text-sm' }} font-semibold text-[var(--desert-rock)] transition hover:bg-[rgba(230,218,200,.3)]">
        View consent terms
    </button>

    <dialog id="{{ $termsModalId }}"
            class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl border border-[rgba(164,141,120,.22)] bg-[var(--feather-white)] p-0 text-[var(--ink)] shadow-2xl backdrop:bg-black/45 backdrop:backdrop-blur-sm">
        <div class="flex items-start justify-between gap-4 border-b border-[rgba(164,141,120,.18)] px-5 py-4">
            <div>
                <h4 class="text-lg font-semibold">Client Consent Terms</h4>
                <p class="mt-1 text-xs text-[var(--muted)]">Please read these terms carefully before agreeing.</p>
            </div>
            <button type="button" onclick="document.getElementById('{{ $termsModalId }}').close()"
                    class="rounded-lg px-3 py-1.5 text-sm font-semibold text-[var(--muted)] hover:bg-[rgba(230,218,200,.4)]">
                Close
            </button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto px-5 py-5">
            <p class="text-sm leading-relaxed text-[var(--muted)]">
                The selected service and this consent will be recorded with the appointment.
            </p>
            <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm leading-relaxed text-[var(--ink)]">
                <li>I voluntarily request the selected service.</li>
                <li>I have disclosed relevant allergies, health conditions, medications, pregnancy, or skin and nail concerns.</li>
                <li>I understand the service has been explained to me and I may ask questions before treatment.</li>
                <li>I understand I may pause or stop the service at any time.</li>
                <li>I agree to follow the aftercare guidance provided by Martinis and Manicures.</li>
            </ol>
        </div>

        <div class="border-t border-[rgba(164,141,120,.18)] px-5 py-4 text-right">
            <button type="button" onclick="document.getElementById('{{ $termsModalId }}').close()"
                    class="rounded-xl bg-[var(--desert-rock)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#927865]">
                I understand
            </button>
        </div>
    </dialog>

    <label class="mt-4 flex items-start gap-2.5 {{ $compact ? 'text-xs' : 'text-sm' }} font-medium text-[var(--ink)]">
        <input id="{{ $acceptedInputId }}" name="consent_accepted" value="1" type="checkbox"
               class="mt-0.5 h-4 w-4 shrink-0 rounded accent-[var(--desert-rock)]">
        <span>
            I have read and agree to the
            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); document.getElementById('{{ $termsModalId }}').showModal()"
                    class="font-semibold text-[var(--desert-rock)] underline underline-offset-2">
                consent terms
            </button>.
        </span>
    </label>

    <p class="mt-4 {{ $compact ? 'text-xs' : 'text-sm' }} font-semibold text-[var(--ink)]">Client signature</p>
    <div class="mt-2 rounded-xl border border-[rgba(164,141,120,.22)] bg-white p-2">
        <canvas id="{{ $canvasId }}" class="{{ $compact ? 'h-36' : 'h-40' }} w-full touch-none rounded-lg bg-white"></canvas>
    </div>
    <input type="hidden" id="{{ $signatureInputId }}" name="consent_signature">

    <div class="mt-3 flex items-center justify-between gap-3">
        <p id="{{ $hintId }}" class="{{ $compact ? 'text-xs text-amber-700' : 'field-hint hidden' }}"></p>
        <button type="button" onclick="{{ $clearFunction }}()"
                class="{{ $compact ? 'text-xs' : 'text-sm' }} font-semibold text-[var(--desert-rock)]">
            Clear signature
        </button>
    </div>
</div>
