@props(['method'])

{{-- Logo d'un moyen de paiement. Pour utiliser un logo officiel, déposer
     public/images/payments/{mtn_momo|moov_money|celtiis_cash|card}.(svg|png|webp) :
     il remplace automatiquement la pastille stylisée ci-dessous. --}}
@php
    $file = collect(['svg', 'png', 'webp', 'jpg'])
        ->map(fn ($ext) => "images/payments/{$method}.{$ext}")
        ->first(fn ($path) => file_exists(public_path($path)));
@endphp

@if($file)
    <img src="{{ asset($file) }}" alt="" {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <svg viewBox="0 0 56 36" role="img" aria-hidden="true" {{ $attributes }}>
        @switch($method)
            @case('mtn_momo')
                <rect width="56" height="36" rx="8" fill="#FFCB05"/>
                <ellipse cx="28" cy="18" rx="22" ry="12" fill="none" stroke="#0F172A" stroke-width="2"/>
                <text x="28" y="23" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-weight="800" font-size="14" fill="#0F172A">MTN</text>
                @break
            @case('moov_money')
                <rect width="56" height="36" rx="8" fill="#0B5CAB"/>
                <text x="28" y="20" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-weight="800" font-size="13" fill="#FFFFFF">moov</text>
                <rect x="10" y="26" width="36" height="3" rx="1.5" fill="#F7941D"/>
                @break
            @case('celtiis_cash')
                <rect width="56" height="36" rx="8" fill="#0F766E"/>
                <text x="28" y="22" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-weight="800" font-size="11" fill="#FFFFFF">celtiis</text>
                @break
            @default
                <rect width="56" height="36" rx="8" fill="#FFFFFF" stroke="#DDE6F0" stroke-width="1.5"/>
                <text x="28" y="15" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-style="italic" font-weight="800" font-size="12" fill="#1A1F71">VISA</text>
                <circle cx="23" cy="26" r="6" fill="#EB001B"/>
                <circle cx="33" cy="26" r="6" fill="#F79E1B" fill-opacity="0.9"/>
        @endswitch
    </svg>
@endif
