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
             class="w-full h-full object-cover transition group-hover:opacity-90"
             loading="lazy">
        <span class="absolute top-2 left-2 bg-ink-900/70 text-cream-50 text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded">
            Publicité
        </span>
    </a>

    @if($wrapperClass)
        </div>
    @endif
@endif
