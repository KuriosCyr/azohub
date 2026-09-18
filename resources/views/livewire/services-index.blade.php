<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">
                @if($category)
                    {{ $categories->where('slug', $category)->first()?->name ?? 'Tous les services' }}
                @else
                    Tous les services
                @endif
            </h1>
            <p class="text-ink-500">
                {{ $services->total() }} service(s) disponible(s)
            </p>
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            {{-- Sidebar Filtres --}}
            <aside class="lg:col-span-1">
                <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 sticky top-24">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">Filtres</h2>
                        <button wire:click="resetFilters" class="text-sm text-terracotta-600 hover:text-terracotta-700 font-semibold">
                            Réinitialiser
                        </button>
                    </div>

                    {{-- Recherche --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Rechercher
                        </label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Mot-clé..."
                            class="w-full px-4 py-3 rounded-xl border border-ink-200 focus:ring-2 focus:ring-terracotta-600 focus:border-transparent"
                        >
                    </div>

                    {{-- Catégorie --}}
                    <div class="mb-6" wire:ignore>
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Catégorie
                        </label>
                        <select
                            wire:model.live="category"
                            class="searchable-select w-full"
                        >
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ville --}}
                    <div class="mb-6" wire:ignore>
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Ville
                        </label>
                        <select
                            wire:model.live="city"
                            class="searchable-select w-full"
                        >
                            <option value="">Toutes les villes</option>
                            @foreach($cities as $cityOption)
                                <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Prix --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Prix (FCFA)
                        </label>
                        <div class="flex gap-2">
                            <input
                                type="number"
                                wire:model.live.debounce.500ms="minPrice"
                                placeholder="Min"
                                class="w-1/2 px-4 py-3 rounded-xl border border-ink-200 focus:ring-2 focus:ring-terracotta-600 focus:border-transparent"
                            >
                            <input
                                type="number"
                                wire:model.live.debounce.500ms="maxPrice"
                                placeholder="Max"
                                class="w-1/2 px-4 py-3 rounded-xl border border-ink-200 focus:ring-2 focus:ring-terracotta-600 focus:border-transparent"
                            >
                        </div>
                    </div>

                    {{-- Note minimum --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Note minimum
                        </label>
                        <div class="grid grid-cols-5 gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    wire:click="$set('minRating', {{ $i }})"
                                    class="px-2 py-2 rounded-lg border text-xs {{ $minRating == $i ? 'bg-terracotta-600 border-terracotta-700 text-cream-50 font-bold' : 'bg-cream-50 border-ink-200 text-ink-700' }} transition hover:border-terracotta-600"
                                >
                                    {{ $i }}<svg class="w-3 h-3 inline-block ml-0.5 text-ochre-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </button>
                            @endfor
                        </div>
                        @if($minRating)
                            <button wire:click="$set('minRating', '')" class="text-xs text-terracotta-600 hover:text-terracotta-700 mt-2 font-semibold">
                                × Effacer le filtre
                            </button>
                        @endif
                    </div>
                </div>

                <x-ad-banner placement="services_sidebar" class="h-64 mt-6" />
            </aside>

            {{-- Liste des services --}}
            <main class="lg:col-span-3">
                {{-- Tri --}}
                <div class="bg-cream-50 rounded-lg p-4 shadow-sm mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-ink-500">Trier par:</span>
                        <select
                            wire:model.live="sortBy"
                            class="px-4 py-2 rounded-lg border border-ink-200 text-sm font-semibold focus:ring-2 focus:ring-terracotta-600 focus:border-transparent"
                        >
                            <option value="recent">Plus récents</option>
                            <option value="popular">Populaires</option>
                            <option value="rating">Mieux notés</option>
                            <option value="price_low">Prix croissant</option>
                            <option value="price_high">Prix décroissant</option>
                        </select>
                    </div>

                    <div class="text-sm text-ink-500">
                        {{ $services->total() }} résultat(s)
                    </div>
                </div>

                {{-- Grille des services --}}
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($services as $service)
                        <a href="{{ route('services.show', $service) }}"
                           x-data x-intersect.once="$el.classList.add('revealed')"
                           style="transition-delay: {{ ($loop->index % 6) * 70 }}ms"
                           class="reveal bg-cream-50 rounded-xl overflow-hidden border border-ink-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-2 group">
                            {{-- Cover Image --}}
                            <div class="relative h-48 overflow-hidden">
                                <x-service-cover :service="$service" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" />

                                {{-- Category Badge --}}
                                <span class="absolute top-3 left-3 bg-terracotta-600 text-cream-50 px-3 py-1 rounded-full text-xs font-bold uppercase shadow-lg">
                                    {{ $service->category->name }}
                                </span>

                                {{-- Level Badge --}}
                                @if($service->prestataire->level === 'expert')
                                    <span class="absolute top-3 right-3 bg-cream-50 text-ochre-600 px-3 py-1 rounded-full text-xs font-bold shadow-lg flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Expert
                                    </span>
                                @endif

                                {{-- Sponsorisé (avantage Premium) --}}
                                @if($service->is_featured && $service->prestataire_plan_slug === 'premium')
                                    <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 bg-gradient-to-r from-ochre-500 to-ochre-600 text-ink-900 px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                        <x-app-icon name="sparkles" class="w-3 h-3" />
                                        Sponsorisé
                                    </span>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="p-5">
                                <h3 class="font-bold text-lg mb-3 line-clamp-2 group-hover:text-terracotta-600 transition min-h-[3.5rem]">
                                    {{ $service->title }}
                                </h3>

                                {{-- Prestataire Info --}}
                                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-ink-100">
                                    <img src="{{ $service->prestataire->avatar ? Storage::url($service->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($service->prestataire->name) }}"
                                         alt="{{ $service->prestataire->name }}"
                                         class="w-10 h-10 rounded-full border-2 border-ink-100">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-ink-700 truncate flex items-center gap-1.5">
                                            {{ $service->prestataire->name }}
                                            @if($service->prestataire_plan_slug === 'premium')
                                                <span class="inline-flex items-center gap-0.5 bg-gradient-to-r from-ochre-500 to-ochre-600 text-ink-900 px-1.5 py-0.5 rounded text-[10px] font-bold flex-shrink-0">
                                                    <x-app-icon name="sparkles" class="w-2.5 h-2.5" />
                                                    Premium
                                                </span>
                                            @elseif($service->prestataire_plan_slug === 'pro')
                                                <span class="inline-flex items-center gap-0.5 bg-terracotta-600 text-cream-50 px-1.5 py-0.5 rounded text-[10px] font-bold flex-shrink-0">
                                                    Pro
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-ink-400 flex items-center gap-1">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $service->prestataire->city ?? 'Bénin' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Rating & Price --}}
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-ochre-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span class="font-bold text-ink-700">{{ number_format($service->rating, 1) }}</span>
                                        <span class="text-ink-400 text-sm">({{ $service->total_reviews }})</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-ink-400">À partir de</p>
                                        <p class="font-bold text-ink-900">{{ number_format($service->price, 0) }} <span class="text-sm">FCFA</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <div class="w-20 h-20 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-ink-900 mb-2">Aucun service trouvé</h3>
                            <p class="text-ink-500 mb-6">
                                Essayez de modifier vos filtres ou votre recherche
                            </p>
                            <button wire:click="resetFilters" class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition">
                                Réinitialiser les filtres
                            </button>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $services->links() }}
                </div>
            </main>
        </div>
    </div>
</div>