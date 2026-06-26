@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-[var(--muted)]']) }}>
    {{ $value ?? $slot }}
</label>
