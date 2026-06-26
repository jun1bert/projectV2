@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[var(--desert-rock)] text-sm font-medium leading-5 text-[var(--ink)] focus:outline-none focus:border-[var(--desert-rock)] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[var(--muted)] hover:text-[var(--ink)] hover:border-[var(--soft-sandstone)] focus:outline-none focus:text-[var(--ink)] focus:border-[var(--soft-sandstone)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
