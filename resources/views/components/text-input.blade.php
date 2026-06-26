@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border border-[var(--desert-rock)]/25 bg-[var(--feather-white)] px-3 py-2.5 text-[var(--ink)] shadow-sm focus:border-[var(--desert-rock)] focus:ring-[var(--desert-rock)]/25']) }}>
