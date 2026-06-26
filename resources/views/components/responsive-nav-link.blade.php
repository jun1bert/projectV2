@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[var(--desert-rock)] text-start text-base font-medium text-[var(--ink)] bg-[rgba(230,218,200,.45)] focus:outline-none focus:text-[var(--ink)] focus:bg-[rgba(230,218,200,.58)] focus:border-[var(--desert-rock)] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[var(--muted)] hover:text-[var(--ink)] hover:bg-[rgba(230,218,200,.32)] hover:border-[var(--soft-sandstone)] focus:outline-none focus:text-[var(--ink)] focus:bg-[rgba(230,218,200,.32)] focus:border-[var(--soft-sandstone)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
