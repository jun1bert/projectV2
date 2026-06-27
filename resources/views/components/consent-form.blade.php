@props([
    'sectionId',
    'canvasId',
    'signatureInputId',
    'acceptedInputId',
    'hintId',
    'clearFunction',
    'compact' => false,
])

<div id="{{ $sectionId }}" {{ $attributes->class([
    'hidden rounded-2xl border border-[rgba(164,141,120,.22)] bg-[rgba(250,249,246,.8)] p-4',
]) }}>
    <h3 class="{{ $compact ? 'text-sm' : 'text-lg' }} font-semibold text-[var(--ink)]">Client Consent</h3>
    <p class="mt-2 {{ $compact ? 'text-xs' : 'text-sm' }} leading-relaxed text-[var(--muted)]">
        Please read each statement before signing. The selected service will be recorded with this consent.
    </p>

    <ul class="mt-3 list-disc space-y-1.5 pl-5 {{ $compact ? 'text-xs' : 'text-sm' }} leading-relaxed text-[var(--muted)]">
        <li>I voluntarily request the selected service.</li>
        <li>I have disclosed relevant allergies, health conditions, medications, pregnancy, or skin and nail concerns.</li>
        <li>I understand the service has been explained to me and I may ask questions or stop the service at any time.</li>
        <li>I agree to follow the aftercare guidance provided by Martinis and Manicures.</li>
    </ul>

    <label class="mt-4 flex items-start gap-2.5 {{ $compact ? 'text-xs' : 'text-sm' }} font-medium text-[var(--ink)]">
        <input id="{{ $acceptedInputId }}" name="consent_accepted" value="1" type="checkbox"
               class="mt-0.5 h-4 w-4 shrink-0 rounded accent-[var(--desert-rock)]">
        <span>I have read and agree to the statements above.</span>
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
