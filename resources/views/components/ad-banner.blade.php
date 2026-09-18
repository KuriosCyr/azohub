@props(['placement', 'class' => '', 'wrapperClass' => null])

@php
    $ad = \App\Models\Advertisement::active()->forPlacement($placement)->orderBy('order')->first();
@endphp

@if($ad)
    @php($ad->recordImpression())
    @if($wrapperClass)
        <div class="{{ $wrapperClass }}">
    @endif

    <a href="{{ route('ads.click', $ad) }}"
       target="_blank"
       rel="noopener sponsored"
       class="relative block rounded-xl overflow-hidden border border-ink-100 group {{ $class }}">
        <img src="{{ \Illuminate\Support\Facades\Storage::url($ad->image) }}"
             alt="{{ $ad->advertiser_name }}"
             class="absolute inset-0 w-full h-full object-cover transition group-hover:opacity-90"
             loading="lazy">

        <span class="absolute top-2 left-2 bg-ink-900/70 text-cream-50 text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded">
            Publicité
        </span>

        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-900/85 via-ink-900/40 to-transparent p-3 pt-8">
            <p class="text-cream-50 font-bold text-sm leading-tight truncate">{{ $ad->advertiser_name }}</p>
            <p class="text-cream-100/80 text-xs mt-0.5">
                En savoir plus →
            </p>
        </div>
    </a>

    @if($wrapperClass)
        </div>
    @endif
@endif
