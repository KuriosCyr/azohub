@props(['class' => 'h-9 w-auto', 'dark' => false])

{{-- Azohub horizontal lockup: symbol + wordmark + tricolor accent bars. From Claude Design. --}}
<svg viewBox="0 0 300 80" fill="none" {{ $attributes->except('class')->merge(['class' => $class]) }}>
    <g transform="translate(0 4) scale(1.19)">
        <g transform="translate(4 4)">
            <g transform="rotate(45 20 20)">
                <path d="M20 0A20 20 0 0 1 40 20L40 36A4 4 0 0 1 36 40L20 40A20 20 0 0 1 0 20A20 20 0 0 1 20 0Z" fill="{{ $dark ? '#FFFFFF' : '#0F2A5C' }}" />
            </g>
            <circle cx="20" cy="20" r="7.6" fill="{{ $dark ? '#2563EB' : '#38BDF8' }}" />
        </g>
    </g>
    <text x="78" y="52" font-family="'Plus Jakarta Sans', Helvetica, Arial, sans-serif" font-weight="800" font-size="58" letter-spacing="-2.03" fill="{{ $dark ? '#FFFFFF' : '#0F2A5C' }}">Azo<tspan fill="{{ $dark ? '#38BDF8' : '#2563EB' }}">hub</tspan></text>
    <g>
        <rect x="78" y="62" width="68.53" height="4" rx="2" fill="#12A150" />
        <rect x="146.53" y="62" width="68.53" height="4" fill="#F5C518" />
        <rect x="215.07" y="62" width="68.53" height="4" rx="2" fill="#E03A2F" />
    </g>
</svg>
