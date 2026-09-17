@props(['class' => 'w-8 h-8'])

<svg viewBox="0 0 32 32" fill="none" {{ $attributes->except('class')->merge(['class' => $class]) }}>
    <rect width="32" height="32" rx="9" fill="#2563EB" />
    <path d="M16 8.5C12.9624 8.5 10.5 10.9624 10.5 14C10.5 18.75 16 24.5 16 24.5C16 24.5 21.5 18.75 21.5 14C21.5 10.9624 19.0376 8.5 16 8.5Z" stroke="#F7FAFD" stroke-width="1.4" stroke-linejoin="round" />
    <path d="M13.2 14L15.2 16L18.8 11.8" stroke="#F7FAFD" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
</svg>
