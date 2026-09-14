@props(['service', 'class' => 'w-full h-full object-cover'])

@php
$categoryStyles = [
    'BTP & Travaux' => ['icon' => 'wrench', 'from' => 'from-blue-500', 'to' => 'to-blue-700'],
    'Digital & Tech' => ['icon' => 'laptop', 'from' => 'from-indigo-500', 'to' => 'to-purple-600'],
    'Maison & Jardinage' => ['icon' => 'home', 'from' => 'from-green-500', 'to' => 'to-emerald-600'],
    'Éducation & Formation' => ['icon' => 'academic-cap', 'from' => 'from-yellow-400', 'to' => 'to-orange-500'],
    'Événementiel' => ['icon' => 'sparkles', 'from' => 'from-pink-500', 'to' => 'to-rose-600'],
    'Transport & Livraison' => ['icon' => 'truck', 'from' => 'from-cyan-500', 'to' => 'to-sky-600'],
    'Beauté & Bien-être' => ['icon' => 'heart', 'from' => 'from-rose-400', 'to' => 'to-pink-600'],
    'Mécanique & Automobile' => ['icon' => 'wrench', 'from' => 'from-slate-500', 'to' => 'to-gray-700'],
    'Administration & Juridique' => ['icon' => 'briefcase', 'from' => 'from-blue-700', 'to' => 'to-slate-800'],
    'Santé & Social' => ['icon' => 'shield-check', 'from' => 'from-teal-500', 'to' => 'to-emerald-700'],
];

$categoryName = $service->category->name ?? null;
$style = $categoryStyles[$categoryName] ?? ['icon' => 'box', 'from' => 'from-blue-500', 'to' => 'to-blue-700'];

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
        <div class="{{ $class }} bg-gradient-to-br {{ $style['from'] }} {{ $style['to'] }} flex items-center justify-center">
            <x-app-icon name="{{ $style['icon'] }}" class="w-1/3 h-1/3 text-white/90" />
        </div>
    </template>
@else
    <div class="{{ $class }} bg-gradient-to-br {{ $style['from'] }} {{ $style['to'] }} flex items-center justify-center">
        <x-app-icon name="{{ $style['icon'] }}" class="w-1/3 h-1/3 text-white/90" />
    </div>
@endif
