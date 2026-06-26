<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-xl border border-[var(--desert-rock)]/30 bg-[var(--feather-white)] px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-[var(--ink)] shadow-sm transition hover:bg-[var(--creamed-oat)] focus:outline-none focus:ring-2 focus:ring-[var(--desert-rock)]/25 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
