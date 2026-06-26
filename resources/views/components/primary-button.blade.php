<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-xl border border-transparent bg-[var(--desert-rock)] px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-[var(--feather-white)] transition hover:bg-[#8f7663] focus:outline-none focus:ring-2 focus:ring-[var(--desert-rock)]/35 active:scale-[.98]']) }}>
    {{ $slot }}
</button>
