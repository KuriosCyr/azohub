@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ink-200 focus:border-terracotta-600 focus:ring-terracotta-600 rounded-lg bg-cream-50 text-ink-900']) }}>
