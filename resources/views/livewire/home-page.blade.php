<div>
    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 text-white py-20 relative overflow-hidden">
        {{-- Décoration de fond --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 right-0 w-1/2 h-full opacity-5">
                <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <defs><pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="white"/></pattern></defs>
                    <rect width="400" height="400" fill="url(#dots)"/>
                </svg>
            </div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-yellow-400 rounded-full opacity-5 blur-3xl -translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                {{-- Colonne Texte --}}
                <div>
                    <div class="inline-flex items-center gap-2 bg-yellow-400/10 border border-yellow-400/30 text-yellow-300 px-4 py-2 rounded-full font-semibold text-sm mb-6">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Plateforme n°1 au Bénin
                    </div>

                    <h1 class="text-5xl md:text-6xl font-black mb-6 leading-tight">
                        Le talent <span class="italic">sans frontière,</span><br>
                        <span class="text-yellow-400">la confiance en plus</span>
                    </h1>

                    <p class="text-xl md:text-2xl mb-8 text-blue-100 font-light">
                        Connectez-vous avec les meilleurs prestataires de services à travers tout le Bénin
                    </p>

                    {{-- Search Bar --}}
                    <form wire:submit.prevent="searchServices" class="bg-white rounded-2xl p-2 shadow-2xl">
                        <div class="flex flex-col md:flex-row gap-2">
                            {{-- Recherche --}}
                            <div class="flex-1 flex items-center gap-3 px-4">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="text"
                                    wire:model="search"
                                    placeholder="Quel service cherchez-vous ?"
                                    class="w-full py-3 text-gray-800 border-0 focus:ring-0 focus:outline-none text-base"
                                >
                            </div>

                            {{-- Séparateur vertical --}}
                            <div class="hidden md:block w-px bg-gray-200 my-2"></div>

                            {{-- Ville --}}
                            <div class="flex items-center gap-3 px-4">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <select
                                    wire:model="city"
                                    class="py-3 text-gray-700 border-0 focus:ring-0 focus:outline-none bg-transparent text-base w-44"
                                >
                                    <option value="">Toutes les villes</option>
                                    @php
                                        $communes = config('communes', []);
                                        $allCities = [];
                                        foreach ($communes as $dept => $villes) {
                                            foreach ($villes as $ville) { $allCities[] = $ville; }
                                        }
                                        sort($allCities);
                                    @endphp
                                    @foreach($allCities as $ville)
                                        <option value="{{ $ville }}">{{ $ville }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Bouton --}}
                            <button
                                type="submit"
                                class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3.5 rounded-xl transition whitespace-nowrap flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Chercher
                            </button>
                        </div>
                    </form>

                    {{-- Stats rapides --}}
                    <div class="mt-8 flex flex-wrap gap-6">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-green-400 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-blue-200 text-sm font-medium">{{ number_format($stats['services']) }}+ services</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-green-400 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-blue-200 text-sm font-medium">{{ number_format($stats['prestataires']) }}+ prestataires</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-green-400 flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-blue-200 text-sm font-medium">{{ number_format($stats['orders']) }}+ missions réussies</span>
                        </div>
                    </div>
                </div>

                {{-- Colonne Image --}}
                <div class="hidden md:block relative">
                    <div class="relative">
                        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=500&h=560&fit=crop"
                                alt="Professionnel au Bénin"
                                class="w-full h-auto"
                            >
                        </div>
                        {{-- Badge vérifié --}}
                        <div class="absolute -top-3 -right-3 bg-yellow-400 text-blue-900 px-5 py-2.5 rounded-full font-black shadow-xl text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Prestataire vérifié
                        </div>
                        {{-- Carte note flottante --}}
                        <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-xl px-5 py-3 flex items-center gap-3">
                            <div class="flex">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                            <div>
                                <p class="font-black text-gray-900 text-sm">4.9/5</p>
                                <p class="text-xs text-gray-500">Satisfaction client</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-10 -left-6 w-24 h-24 bg-yellow-400 rounded-full opacity-20 blur-2xl"></div>
                </div>
            </div>
        </div>

        {{-- Vague de séparation --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 60L60 52.5C120 45 240 30 360 22.5C480 15 600 15 720 18.75C840 22.5 960 30 1080 33.75C1200 37.5 1320 37.5 1380 37.5L1440 37.5V60H0Z" fill="#F9FAFB"/>
            </svg>
        </div>
    </section>

    {{-- Categories Section --}}
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider mb-2">Explorez</p>
                <h2 class="text-4xl font-black text-gray-900 mb-3">Nos catégories de services</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">
                    Des milliers de prestataires qualifiés dans toutes les catégories
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                       class="bg-white rounded-2xl p-6 text-center hover:shadow-xl transition-all duration-300 group border border-gray-100 hover:border-blue-200">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center text-blue-700 transition">
                            @php
                                $catIcons = [
                                    'Hammer'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>',
                                    'Code'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>',
                                    'Home'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
                                    'GraduationCap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>',
                                    'Calendar'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
                                    'Truck'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>',
                                    'Sparkles'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>',
                                    'Wrench'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z"/>',
                                    'FileText'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                                    'Heart'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>',
                                ];
                                $svgPath = $catIcons[$category->icon] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>';
                            @endphp
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svgPath !!}</svg>
                        </div>
                        <h3 class="font-bold text-gray-800 group-hover:text-blue-700 transition text-sm">
                            {{ $category->name }}
                        </h3>
                        @if($category->description)
                            <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $category->description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('services.index') }}" class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3.5 rounded-full transition">
                    Voir toutes les catégories
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Popular Services Section --}}
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider mb-2">Top services</p>
                    <h2 class="text-4xl font-black text-gray-900 mb-2">Services les plus demandés</h2>
                    <p class="text-gray-500">Découvrez les prestataires les mieux notés</p>
                </div>
                <a href="{{ route('services.index') }}" class="hidden md:flex items-center gap-1 text-blue-600 hover:text-blue-800 font-bold text-sm">
                    Tout voir
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($popularServices as $service)
                    <a href="{{ route('services.show', $service) }}"
                       class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group border border-gray-100">
                        {{-- Cover Image --}}
                        <div class="relative h-48 overflow-hidden">
                            <x-service-cover :service="$service" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />

                            {{-- Category Badge --}}
                            <span class="absolute top-3 left-3 bg-yellow-400 text-blue-900 px-3 py-1 rounded-full text-xs font-bold shadow">
                                {{ $service->category->name }}
                            </span>

                            {{-- Level Badge --}}
                            @if($service->prestataire->level === 'expert')
                                <span class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm text-blue-900 px-2.5 py-1 rounded-full text-xs font-bold shadow flex items-center gap-1">
                                    <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Expert
                                </span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-700 transition min-h-[3rem] text-sm leading-snug">
                                {{ $service->title }}
                            </h3>

                            {{-- Prestataire --}}
                            <div class="flex items-center gap-2.5 mb-4 pb-4 border-b border-gray-100">
                                <img src="{{ $service->prestataire->avatar ? Storage::url($service->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($service->prestataire->name) }}"
                                     alt="{{ $service->prestataire->name }}"
                                     class="w-8 h-8 rounded-full border border-gray-200">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $service->prestataire->name }}</p>
                                    @if($service->prestataire->city)
                                        <div class="flex items-center gap-1 text-xs text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $service->prestataire->city }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Rating & Price --}}
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    <span class="font-bold text-gray-800 text-sm">{{ number_format($service->rating, 1) }}</span>
                                    <span class="text-gray-400 text-xs">({{ $service->total_reviews }})</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">À partir de</p>
                                    <p class="font-black text-blue-900">{{ number_format($service->price, 0) }} <span class="text-xs font-semibold">FCFA</span></p>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                        </div>
                        <p class="text-xl text-gray-500 mb-4 font-semibold">Aucun service disponible pour le moment</p>
                        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-bold">
                            Soyez le premier à proposer vos services →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-20 bg-blue-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-400 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-400 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-black mb-3">Azohub en chiffres</h2>
                <p class="text-blue-300">La confiance de milliers de Béninois</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                    <p class="text-5xl font-black text-yellow-400 mb-2">{{ number_format($stats['services']) }}+</p>
                    <p class="text-lg font-semibold mb-1">Services disponibles</p>
                    <p class="text-blue-300 text-sm">Dans toutes les catégories</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                    <p class="text-5xl font-black text-yellow-400 mb-2">{{ number_format($stats['prestataires']) }}+</p>
                    <p class="text-lg font-semibold mb-1">Prestataires qualifiés</p>
                    <p class="text-blue-300 text-sm">Vérifiés et notés</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                    <p class="text-5xl font-black text-yellow-400 mb-2">{{ number_format($stats['orders']) }}+</p>
                    <p class="text-lg font-semibold mb-1">Missions réussies</p>
                    <p class="text-blue-300 text-sm">Avec satisfaction garantie</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Comment ça marche --}}
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-14">
                <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider mb-2">Simple & rapide</p>
                <h2 class="text-4xl font-black text-gray-900 mb-3">Comment ça marche ?</h2>
                <p class="text-gray-500 text-lg">Trouvez le bon prestataire en 3 étapes</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl bg-blue-900 text-white flex items-center justify-center mx-auto mb-5 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-yellow-400 text-blue-900 font-black text-sm flex items-center justify-center mx-auto -mt-3 mb-4 relative -top-1">1</div>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Cherchez</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Parcourez nos catégories ou utilisez la recherche pour trouver le service dont vous avez besoin.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl bg-yellow-400 text-blue-900 flex items-center justify-center mx-auto mb-5 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-blue-900 text-white font-black text-sm flex items-center justify-center mx-auto -mt-3 mb-4 relative -top-1">2</div>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Commandez</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Choisissez votre prestataire, passez commande et payez en toute sécurité via notre plateforme.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-2xl bg-green-500 text-white flex items-center justify-center mx-auto mb-5 shadow-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-green-500 text-white font-black text-sm flex items-center justify-center mx-auto -mt-3 mb-4 relative -top-1">3</div>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Validez</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Recevez la livraison, validez le travail et laissez un avis. Le paiement est débloqué pour le prestataire.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-yellow-400 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <defs><pattern id="grid" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/></pattern></defs>
                <rect width="200" height="200" fill="url(#grid)"/>
            </svg>
        </div>

        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-5xl font-black text-blue-900 mb-5">
                    Vous êtes un professionnel ?
                </h2>
                <p class="text-xl text-blue-800 mb-10 leading-relaxed">
                    Rejoignez {{ number_format($stats['prestataires']) }}+ prestataires et commencez à gagner de l'argent avec vos compétences
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 bg-blue-900 hover:bg-blue-800 text-white font-bold px-10 py-4 rounded-full text-lg transition shadow-2xl">
                        Devenir prestataire
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('how-it-works') }}"
                       class="inline-flex items-center justify-center gap-2 bg-white/80 hover:bg-white text-blue-900 font-bold px-10 py-4 rounded-full text-lg transition">
                        En savoir plus
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-6 text-blue-800 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Inscription gratuite
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Paiements sécurisés
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Support disponible
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
