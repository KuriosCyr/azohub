<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-terracotta-600 border border-transparent rounded-lg font-semibold text-xs text-cream-50 uppercase tracking-widest hover:bg-terracotta-700 focus:bg-terracotta-700 active:bg-terracotta-700 focus:outline-none focus:ring-2 focus:ring-terracotta-600 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
