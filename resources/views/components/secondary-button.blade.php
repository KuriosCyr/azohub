<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-cream-50 border border-ink-200 rounded-lg font-semibold text-xs text-ink-700 uppercase tracking-widest hover:bg-ink-100/30 focus:outline-none focus:ring-2 focus:ring-terracotta-600 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
