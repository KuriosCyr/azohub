<div>
    {{-- Hero Section --}}
    <section class="bg-cream relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image: radial-gradient(#94A3B8 1px, transparent 1px); background-size: 22px 22px;"></div>

        <div class="container mx-auto px-4 relative py-16 md:py-24">
            <div class="grid md:grid-cols-2 gap-16 items-end">
                {{-- Colonne Texte --}}
                <div x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 50)"
                     :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
                     class="transition-all duration-700 ease-out">
                    <div class="inline-flex items-center gap-2 mb-7">
                        <div class="w-7 h-0.5 bg-terracotta-600"></div>
                        <span class="text-xs font-semibold tracking-widest uppercase text-terracotta-600">Services locaux au Bénin</span>
                    </div>

                    <h1 class="font-serif text-5xl md:text-6xl font-medium leading-[1.05] text-ink-900 mb-6">
                        Le savoir-faire d'ici,<br>à portée de main.
                    </h1>

                    <p class="text-lg text-ink-500 max-w-md mb-10 leading-relaxed">
                        Plombiers, développeurs, professeurs, décorateurs&hellip; trouvez un prestataire vérifié près de chez vous, du premier échange au paiement sécurisé.
                    </p>

                    {{-- Search Bar --}}
                    <form wire:submit.prevent="searchServices" class="bg-cream-50 border border-ink-100 rounded-lg p-2 mb-10">
                        <div class="flex flex-col md:flex-row gap-2">
                            <div class="flex-1 flex items-center gap-3 px-4">
                                <x-app-icon name="search" class="w-5 h-5 text-ink-300 flex-shrink-0" />
                                <input
                                    type="text"
                                    wire:model="search"
                                    placeholder="Quel service cherchez-vous ?"
                                    class="w-full py-3 text-ink-900 border-0 focus:ring-0 focus:outline-none text-base bg-transparent placeholder:text-ink-300"
                                >
                            </div>

                            <div class="hidden md:block w-px bg-ink-100 my-2"></div>

                            <div class="flex items-center gap-3 px-4">
                                <x-app-icon name="map-pin" class="w-5 h-5 text-ink-300 flex-shrink-0" />
                                <select
                                    wire:model="city"
                                    class="py-3 text-ink-700 border-0 focus:ring-0 focus:outline-none bg-transparent text-base w-44"
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

                            <button
                                type="submit"
                                class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-semibold px-8 py-3.5 rounded-lg transition whitespace-nowrap flex items-center justify-center gap-2"
                            >
                                <x-app-icon name="search" class="w-4 h-4" />
                                Chercher
                            </button>
                        </div>
                    </form>

                    {{-- Stats rapides --}}
                    <div class="flex gap-10 pt-6 border-t border-ink-100">
                        <div>
                            <div class="font-serif text-2xl font-medium text-ink-900">{{ number_format($stats['services']) }}+</div>
                            <div class="text-xs text-ink-400 mt-1">services disponibles</div>
                        </div>
                        <div>
                            <div class="font-serif text-2xl font-medium text-ink-900">{{ number_format($stats['prestataires']) }}+</div>
                            <div class="text-xs text-ink-400 mt-1">prestataires vérifiés</div>
                        </div>
                        <div>
                            <div class="font-serif text-2xl font-medium text-ink-900">{{ number_format($stats['orders']) }}+</div>
                            <div class="text-xs text-ink-400 mt-1">missions réussies</div>
                        </div>
                    </div>
                </div>

                {{-- Colonne décorative --}}
                <div class="hidden md:grid grid-cols-2 gap-4">
                    <div class="h-56 rounded-lg bg-ink-900 flex items-end p-5 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1765378025221-3ed7eadc6def?w=500&h=600&fit=crop&q=80" alt="" class="absolute inset-0 w-full h-full object-cover opacity-55 mix-blend-luminosity" loading="lazy">
                        <div class="absolute inset-0 bg-ink-900/40"></div>
                        <x-app-icon name="wrench" class="w-8 h-8 text-cream-50 relative" />
                    </div>
                    <div class="h-40 mt-16 rounded-lg bg-terracotta-700 flex items-end p-5 overflow-hidden relative" style="animation: float 7s ease-in-out infinite; animation-delay: .3s;">
                        <img src="https://images.unsplash.com/photo-1740657254989-42fe9c3b8cce?w=500&h=400&fit=crop&q=80" alt="" class="absolute inset-0 w-full h-full object-cover opacity-55 mix-blend-luminosity" loading="lazy">
                        <div class="absolute inset-0 bg-terracotta-700/40"></div>
                        <x-app-icon name="home" class="w-7 h-7 text-cream-50 relative" />
                    </div>
                    <div class="h-40 rounded-lg bg-clay-600 flex items-end p-5 overflow-hidden relative" style="animation: float 8s ease-in-out infinite; animation-delay: 1s;">
                        <img src="https://images.unsplash.com/photo-1618477388954-7852f32655ec?w=500&h=400&fit=crop&q=80" alt="" class="absolute inset-0 w-full h-full object-cover opacity-55 mix-blend-luminosity" loading="lazy">
                        <div class="absolute inset-0 bg-clay-600/40"></div>
                        <x-app-icon name="laptop" class="w-7 h-7 text-cream-50 relative" />
                    </div>
                    <div class="h-56 -mt-16 rounded-lg bg-terracotta-600 flex items-end p-5 overflow-hidden relative">
                        <img src="https://images.unsplash.com/photo-1653821355736-0c2598d0a63e?w=500&h=600&fit=crop&q=80" alt="" class="absolute inset-0 w-full h-full object-cover opacity-55 mix-blend-luminosity" loading="lazy">
                        <div class="absolute inset-0 bg-terracotta-600/40"></div>
                        <x-app-icon name="sparkles" class="w-8 h-8 text-cream-50 relative" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories Section --}}
    <section class="py-20 bg-cream-50 border-t border-ink-100">
        <div class="container mx-auto px-4">
            <div class="flex items-end justify-between mb-12">
                <div>
                    <p class="text-terracotta-600 font-semibold text-xs uppercase tracking-widest mb-3">Explorez</p>
                    <h2 class="font-serif text-3xl md:text-4xl font-medium text-ink-900">Nos catégories de services</h2>
                </div>
                <a href="{{ route('services.index') }}" class="hidden md:inline text-sm font-semibold text-terracotta-600 hover:text-terracotta-700">
                    Voir tout &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                       x-data x-intersect.once="$el.classList.add('revealed')"
                       style="transition-delay: {{ $loop->index * 60 }}ms"
                       class="reveal bg-cream-50 rounded-lg p-6 text-center hover:shadow-md hover:-translate-y-1 transition-all duration-300 group border border-ink-100">
                        <div class="w-12 h-12 mx-auto mb-4 rounded-lg bg-terracotta-50 group-hover:bg-terracotta-100 flex items-center justify-center text-terracotta-700 transition">
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
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svgPath !!}</svg>
                        </div>
                        <h3 class="font-semibold text-ink-900 group-hover:text-terracotta-700 transition text-sm">
                            {{ $category->name }}
                        </h3>
                        @if($category->description)
                            <p class="text-xs text-ink-400 mt-1 line-clamp-2">{{ $category->description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-10 md:hidden">
                <a href="{{ route('services.index') }}" class="inline-flex items-center gap-2 bg-ink-900 hover:bg-ink-700 text-cream-50 font-semibold px-8 py-3 rounded-lg transition">
                    Voir toutes les catégories
                </a>
            </div>
        </div>
    </section>

    {{-- Popular Services Section --}}
    <section class="py-20 bg-cream">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <p class="text-terracotta-600 font-semibold text-xs uppercase tracking-widest mb-3">Sélection du moment</p>
                    <h2 class="font-serif text-3xl md:text-4xl font-medium text-ink-900">Services les plus demandés</h2>
                </div>
                <a href="{{ route('services.index') }}" class="hidden md:flex items-center gap-1 text-terracotta-600 hover:text-terracotta-700 font-semibold text-sm">
                    Tout voir &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($popularServices as $service)
                    <a href="{{ route('services.show', $service) }}"
                       x-data x-intersect.once="$el.classList.add('revealed')"
                       style="transition-delay: {{ $loop->index * 80 }}ms"
                       class="reveal bg-cream-50 rounded-lg overflow-hidden border border-ink-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
                        {{-- Cover Image --}}
                        <div class="relative h-44 overflow-hidden">
                            <x-service-cover :service="$service" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />

                            <span class="absolute top-3 left-3 bg-cream-50 text-terracotta-700 px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                {{ $service->category->name }}
                            </span>

                            @if($service->prestataire->level === 'expert')
                                <span class="absolute top-3 right-3 bg-ink-900/85 text-cream-50 px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1">
                                    <x-app-icon name="star" class="w-3 h-3 text-ochre-500" />
                                    Expert
                                </span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <h3 class="font-semibold text-ink-900 mb-3 line-clamp-2 group-hover:text-terracotta-700 transition min-h-[2.5rem] text-sm leading-snug">
                                {{ $service->title }}
                            </h3>

                            <div class="flex items-center gap-2.5 mb-4 pb-4 border-b border-ink-100">
                                <img src="{{ $service->prestataire->avatar ? Storage::url($service->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($service->prestataire->name) }}"
                                     alt="{{ $service->prestataire->name }}"
                                     class="w-8 h-8 rounded-full border border-ink-100">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-ink-700 text-sm truncate">{{ $service->prestataire->name }}</p>
                                    @if($service->prestataire->city)
                                        <div class="flex items-center gap-1 text-xs text-ink-400">
                                            <x-app-icon name="map-pin" class="w-3 h-3" />
                                            {{ $service->prestataire->city }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1">
                                    <x-app-icon name="star" class="w-4 h-4 text-ochre-500" />
                                    <span class="font-semibold text-ink-900 text-sm">{{ number_format($service->rating, 1) }}</span>
                                    <span class="text-ink-400 text-xs">({{ $service->total_reviews }})</span>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-ink-400">À partir de</p>
                                    <p class="font-bold text-ink-900">{{ number_format($service->price, 0) }} <span class="text-xs font-medium">FCFA</span></p>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="w-16 h-16 rounded-full bg-terracotta-50 flex items-center justify-center mx-auto mb-4">
                            <x-app-icon name="box" class="w-8 h-8 text-terracotta-600" />
                        </div>
                        <p class="text-lg text-ink-500 mb-4 font-medium">Aucun service disponible pour le moment</p>
                        <a href="{{ route('register') }}" class="text-terracotta-600 hover:text-terracotta-700 font-semibold">
                            Soyez le premier à proposer vos services &rarr;
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-20 bg-ink-900 text-cream-50 relative overflow-hidden">
        <div class="container mx-auto px-4 relative">
            <div class="text-center mb-14">
                <h2 class="font-serif text-3xl md:text-4xl font-medium mb-3">Azohub en chiffres</h2>
                <p class="text-cream-100/50">La confiance de milliers de Béninois</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="bg-cream-50/5 border border-cream-50/10 rounded-lg p-8"
                     x-data="counter({{ (int) $stats['services'] }})" x-intersect.once="start()">
                    <p class="font-serif text-4xl font-medium text-ochre-500 mb-2"><span x-text="display">0</span>+</p>
                    <p class="text-base font-medium mb-1">Services disponibles</p>
                    <p class="text-cream-100/40 text-sm">Dans toutes les catégories</p>
                </div>
                <div class="bg-cream-50/5 border border-cream-50/10 rounded-lg p-8"
                     x-data="counter({{ (int) $stats['prestataires'] }})" x-intersect.once="start()">
                    <p class="font-serif text-4xl font-medium text-ochre-500 mb-2"><span x-text="display">0</span>+</p>
                    <p class="text-base font-medium mb-1">Prestataires qualifiés</p>
                    <p class="text-cream-100/40 text-sm">Vérifiés et notés</p>
                </div>
                <div class="bg-cream-50/5 border border-cream-50/10 rounded-lg p-8"
                     x-data="counter({{ (int) $stats['orders'] }})" x-intersect.once="start()">
                    <p class="font-serif text-4xl font-medium text-ochre-500 mb-2"><span x-text="display">0</span>+</p>
                    <p class="text-base font-medium mb-1">Missions réussies</p>
                    <p class="text-cream-100/40 text-sm">Avec satisfaction garantie</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Comment ça marche --}}
    <section class="py-20 bg-cream-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-14">
                <p class="text-terracotta-600 font-semibold text-xs uppercase tracking-widest mb-3">Simple &amp; rapide</p>
                <h2 class="font-serif text-3xl md:text-4xl font-medium text-ink-900">Comment ça marche ?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-4xl mx-auto">
                <div class="text-center reveal" x-data x-intersect.once="$el.classList.add('revealed')">
                    <div class="w-14 h-14 rounded-lg bg-forest-600 text-cream-50 flex items-center justify-center mx-auto mb-5">
                        <x-app-icon name="search" class="w-6 h-6" />
                    </div>
                    <div class="font-serif text-terracotta-600 text-sm font-medium mb-3">01</div>
                    <h3 class="text-lg font-semibold text-ink-900 mb-2">Cherchez</h3>
                    <p class="text-ink-500 text-sm leading-relaxed">Parcourez nos catégories ou utilisez la recherche pour trouver le service dont vous avez besoin.</p>
                </div>
                <div class="text-center reveal" x-data x-intersect.once="$el.classList.add('revealed')" style="transition-delay: 120ms">
                    <div class="w-14 h-14 rounded-lg bg-ochre-600 text-cream-50 flex items-center justify-center mx-auto mb-5">
                        <x-app-icon name="check-circle" class="w-6 h-6" />
                    </div>
                    <div class="font-serif text-terracotta-600 text-sm font-medium mb-3">02</div>
                    <h3 class="text-lg font-semibold text-ink-900 mb-2">Commandez</h3>
                    <p class="text-ink-500 text-sm leading-relaxed">Choisissez votre prestataire, passez commande et payez en toute sécurité via notre plateforme.</p>
                </div>
                <div class="text-center reveal" x-data x-intersect.once="$el.classList.add('revealed')" style="transition-delay: 240ms">
                    <div class="w-14 h-14 rounded-lg bg-clay-500 text-cream-50 flex items-center justify-center mx-auto mb-5">
                        <x-app-icon name="check" class="w-6 h-6" />
                    </div>
                    <div class="font-serif text-terracotta-600 text-sm font-medium mb-3">03</div>
                    <h3 class="text-lg font-semibold text-ink-900 mb-2">Validez</h3>
                    <p class="text-ink-500 text-sm leading-relaxed">Recevez la livraison, validez le travail et laissez un avis. Le paiement est débloqué pour le prestataire.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-terracotta-600 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center relative">
            <div class="max-w-2xl mx-auto reveal" x-data x-intersect.once="$el.classList.add('revealed')">
                <h2 class="font-serif text-4xl md:text-5xl font-medium text-cream-50 mb-5">
                    Vous êtes un professionnel&nbsp;?
                </h2>
                <p class="text-lg text-cream-50/85 mb-10 leading-relaxed">
                    Rejoignez {{ number_format($stats['prestataires']) }}+ prestataires et commencez à gagner de l'argent avec vos compétences.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 bg-ink-900 hover:bg-ink-700 text-cream-50 font-semibold px-9 py-3.5 rounded-lg transition">
                        Devenir prestataire
                    </a>
                    <a href="{{ route('how-it-works') }}"
                       class="inline-flex items-center justify-center gap-2 bg-transparent border border-cream-50/50 hover:bg-cream-50/10 text-cream-50 font-semibold px-9 py-3.5 rounded-lg transition">
                        En savoir plus
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap justify-center gap-8 text-cream-50/85 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <x-app-icon name="check" class="w-4 h-4" />
                        Inscription gratuite
                    </div>
                    <div class="flex items-center gap-2">
                        <x-app-icon name="check" class="w-4 h-4" />
                        Paiements sécurisés
                    </div>
                    <div class="flex items-center gap-2">
                        <x-app-icon name="check" class="w-4 h-4" />
                        Support disponible
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
