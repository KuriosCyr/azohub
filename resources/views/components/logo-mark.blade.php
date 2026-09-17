@props(['class' => 'w-8 h-8', 'dark' => false])

{{-- Azohub symbol: rotated rounded shape + accent dot. From the Claude Design lockup. --}}
<svg viewBox="0 0 48 48" fill="none" {{ $attributes->except('class')->merge(['class' => $class]) }}>
    <g transform="translate(4 4)">
        <g transform="rotate(45 20 20)">
            <path d="M20 0A20 20 0 0 1 40 20L40 36A4 4 0 0 1 36 40L20 40A20 20 0 0 1 0 20A20 20 0 0 1 20 0Z" fill="{{ $dark ? '#FFFFFF' : '#0F2A5C' }}" />
        </g>
        <circle cx="20" cy="20" r="7.6" fill="{{ $dark ? '#2563EB' : '#38BDF8' }}" />
    </g>
</svg>
