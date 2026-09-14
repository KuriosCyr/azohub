@props(['service', 'class' => 'w-full h-full object-cover'])

@php
$categoryStyles = [
    'BTP & Travaux' => ['icon' => 'wrench', 'bg' => 'bg-forest-600'],
    'Digital & Tech' => ['icon' => 'laptop', 'bg' => 'bg-ink-700'],
    'Maison & Jardinage' => ['icon' => 'home', 'bg' => 'bg-clay-500'],
    'Éducation & Formation' => ['icon' => 'academic-cap', 'bg' => 'bg-ochre-600'],
    'Événementiel' => ['icon' => 'sparkles', 'bg' => 'bg-terracotta-600'],
    'Transport & Livraison' => ['icon' => 'truck', 'bg' => 'bg-stone-600'],
    'Beauté & Bien-être' => ['icon' => 'heart', 'bg' => 'bg-rose-800'],
    'Mécanique & Automobile' => ['icon' => 'wrench', 'bg' => 'bg-stone-700'],
    'Administration & Juridique' => ['icon' => 'briefcase', 'bg' => 'bg-ink-900'],
    'Santé & Social' => ['icon' => 'shield-check', 'bg' => 'bg-emerald-800'],
];

$categoryName = $service->category->name ?? null;
$style = $categoryStyles[$categoryName] ?? ['icon' => 'box', 'bg' => 'bg-terracotta-600'];

$hasRealImage = filled($service->cover_image);
$imageUrl = $hasRealImage
    ? (\Illuminate\Support\Str::startsWith($service->cover_image, 'http')
        ? $service->cover_image
        : \Illuminate\Support\Facades\Storage::url($service->cover_image))
    : null;
@endphp

@if($imageUrl)
    <img src="{{ $imageUrl }}" alt="{{ $service->title }}" class="{{ $class }}" loading="lazy"
         onerror="this.onerror=null;this.replaceWith(document.getElementById('{{ $service->id }}-cover-fallback').content.cloneNode(true).firstElementChild);">
    <template id="{{ $service->id }}-cover-fallback">
        <div class="{{ $class }} {{ $style['bg'] }} flex items-center justify-center">
            <x-app-icon name="{{ $style['icon'] }}" class="w-1/3 h-1/3 text-cream-50/90" />
        </div>
    </template>
@else
    <div class="{{ $class }} {{ $style['bg'] }} flex items-center justify-center">
        <x-app-icon name="{{ $style['icon'] }}" class="w-1/3 h-1/3 text-cream-50/90" />
    </div>
@endif
