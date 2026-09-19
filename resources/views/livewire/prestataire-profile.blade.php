<div class="min-h-screen bg-cream">
    {{-- Hero/Header du profil --}}
    <div class="bg-ink-900 text-cream-50">
        <div class="container mx-auto px-4 py-12">
            <div class="flex flex-col md:flex-row items-center gap-8">
                {{-- Avatar --}}
                <div class="relative">
                    <img src="{{ $prestataire->avatar ? Storage::url($prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($prestataire->name) . '&size=200' }}"
                         alt="{{ $prestataire->name }}"
                         class="w-32 h-32 md:w-40 md:h-40 rounded-full border-8 border-cream-50 shadow-2xl">

                    @if($prestataire->isOnline())
                        <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-forest-600 border-4 border-cream-50" title="En ligne"></span>
                    @endif

                    {{-- Badge niveau --}}
                    @if($prestataire->level === 'expert')
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-terracotta-600 text-cream-50 px-4 py-1 rounded-full text-sm font-bold shadow-lg flex items-center gap-1 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Expert
                        </div>
                    @elseif($prestataire->level === 'confirme' || $prestataire->level === 'professionnel')
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-forest-600 text-cream-50 px-4 py-1 rounded-full text-sm font-bold shadow-lg flex items-center gap-1 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Confirmé
                        </div>
                    @endif
                </div>

                {{-- Infos principales --}}
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl font-serif font-medium mb-2 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        {{ $prestataire->name }}
                        @php $plan = $prestataire->currentPlan(); @endphp
                        @if($plan?->slug === 'premium')
                            <span class="inline-flex items-center gap-1 bg-gradient-to-r from-ochre-500 to-ochre-600 text-ink-900 px-3 py-1 rounded-full text-sm font-bold shadow-lg">
                                <x-app-icon name="sparkles" class="w-3.5 h-3.5" />
                                Premium
                            </span>
                        @elseif($plan?->slug === 'pro')
                            <span class="inline-flex items-center gap-1 bg-terracotta-600 text-cream-50 px-3 py-1 rounded-full text-sm font-bold">
                                <x-app-icon name="star" class="w-3.5 h-3.5" />
                                Pro
                            </span>
                        @endif
                    </h1>

                    <div class="flex flex-wrap justify-center md:justify-start items-center gap-4 mb-4 text-ink-300">
                        @php
                            $availabilityStyles = [
                                'disponible' => ['bg-forest-600', 'Disponible'],
                                'occupe' => ['bg-ochre-500', 'Occupé'],
                                'indisponible' => ['bg-ink-400', 'Indisponible'],
                            ];
                            [$dotColor, $availabilityLabel] = $availabilityStyles[$prestataire->availability] ?? $availabilityStyles['disponible'];
                        @endphp
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                            {{ $availabilityLabel }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $prestataire->city ?? 'Bénin' }}
                        </span>
                        @if($prestataire->identity_verified)
                            <span class="flex items-center gap-1 bg-forest-600 text-cream-50 px-3 py-1 rounded-full text-sm font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Identité vérifiée
                            </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Membre depuis {{ $prestataire->created_at->format('Y') }}
                        </span>
                    </div>

                    @if($prestataire->bio)
                        <p class="text-lg text-ink-300 max-w-2xl">
                            {{ Str::limit($prestataire->bio, 200) }}
                        </p>
                    @endif

                    {{-- Badges --}}
                    @if($prestataire->badges && count($prestataire->badges) > 0)
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($prestataire->badges as $badge)
                                <span class="bg-terracotta-600 text-cream-50 px-3 py-1 rounded-full text-xs font-bold">
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if(!Auth::check() || Auth::user()->isClient())
                        <div class="mt-6">
                            <button wire:click="contactPrestataire" wire:loading.attr="disabled" wire:target="contactPrestataire"
                                    class="inline-flex items-center gap-2 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition shadow-md disabled:opacity-60">
                                <x-app-icon name="chat" class="w-4 h-4" />
                                Contacter le prestataire
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Stats rapides --}}
                <div class="grid grid-cols-3 gap-6 bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-ochre-500">{{ $stats['services_count'] }}</p>
                        <p class="text-sm text-ink-300">Services</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-ochre-500">{{ $stats['total_orders'] }}</p>
                        <p class="text-sm text-ink-300">Commandes</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-ochre-500 flex items-center justify-center gap-1">
                            {{ number_format($stats['rating'], 1) }}
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </p>
                        <p class="text-sm text-ink-300">Note</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats détaillées --}}
    <div class="bg-cream-50 border-b border-ink-100 sticky top-16 z-40 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-ink-900 mb-1 flex items-center justify-center gap-1">
                        {{ number_format($stats['rating'], 1) }}
                        <svg class="w-5 h-5 text-ochre-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </p>
                    <p class="text-sm text-ink-500">Note moyenne</p>
                    <p class="text-xs text-ink-400">({{ $stats['total_reviews'] }} avis)</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-ink-900 mb-1">{{ $stats['response_time'] }}</p>
                    <p class="text-sm text-ink-500">Temps de réponse</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-ink-900 mb-1">{{ $stats['total_orders'] }}</p>
                    <p class="text-sm text-ink-500">Commandes réalisées</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-ink-900 mb-1">{{ $stats['completion_rate'] !== null ? $stats['completion_rate'] . '%' : '—' }}</p>
                    <p class="text-sm text-ink-500">Taux de réussite</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Onglets --}}
    <div class="bg-cream-50 border-b border-ink-100">
        <div class="container mx-auto px-4">
            <div class="flex gap-8">
                <button
                    wire:click="setTab('services')"
                    class="py-4 px-2 font-bold transition border-b-4 {{ $activeTab === 'services' ? 'border-terracotta-600 text-terracotta-600' : 'border-transparent text-ink-500 hover:text-terracotta-600' }}">
                    Services ({{ $stats['services_count'] }})
                </button>
                <button
                    wire:click="setTab('reviews')"
                    class="py-4 px-2 font-bold transition border-b-4 {{ $activeTab === 'reviews' ? 'border-terracotta-600 text-terracotta-600' : 'border-transparent text-ink-500 hover:text-terracotta-600' }}">
                    Avis ({{ $stats['total_reviews'] }})
                </button>
                <button
                    wire:click="setTab('about')"
                    class="py-4 px-2 font-bold transition border-b-4 {{ $activeTab === 'about' ? 'border-terracotta-600 text-terracotta-600' : 'border-transparent text-ink-500 hover:text-terracotta-600' }}">
                    À propos
                </button>
            </div>
        </div>
    </div>

    {{-- Contenu des onglets --}}
    <div class="container mx-auto px-4 py-8">
        {{-- Onglet Services --}}
        @if($activeTab === 'services')
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <a href="{{ route('services.show', $service) }}"
                       x-data x-intersect.once="$el.classList.add('revealed')"
                       style="transition-delay: {{ $loop->index * 70 }}ms"
                       class="reveal bg-cream-50 rounded-xl overflow-hidden border border-ink-100 hover:shadow-md transition-all duration-300 transform hover:-translate-y-2 group">
                        <div class="relative h-48 overflow-hidden">
                            <x-service-cover :service="$service" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" />
                            <span class="absolute top-3 left-3 bg-terracotta-600 text-cream-50 px-3 py-1 rounded-full text-xs font-bold uppercase">
                                {{ $service->category->name }}
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-lg mb-3 line-clamp-2 group-hover:text-terracotta-600 transition min-h-[3.5rem]">
                                {{ $service->title }}
                            </h3>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-ochre-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="font-bold">{{ number_format($service->rating, 1) }}</span>
                                    <span class="text-ink-400 text-sm">({{ $service->total_reviews }})</span>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-ink-900">{{ number_format($service->price, 0) }} <span class="text-sm">FCFA</span></p>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <p class="text-xl text-ink-500">Aucun service disponible pour le moment</p>
                    </div>
                @endforelse
            </div>

            @if($services->hasPages())
                <div class="mt-8">
                    {{ $services->links() }}
                </div>
            @endif
        @endif

        {{-- Onglet Avis --}}
        @if($activeTab === 'reviews')
            <div class="max-w-4xl mx-auto">
                {{-- Statistiques des notes --}}
                @php
                    $allReviews = $prestataire->receivedReviews()
                        ->where('review_type', 'client_to_prestataire')
                        ->get();

                    $reviewStats = [];
                    for ($i = 5; $i >= 1; $i--) {
                        $count = $allReviews->where('rating', $i)->count();
                        $percentage = $allReviews->count() > 0 ? ($count / $allReviews->count()) * 100 : 0;
                        $reviewStats[$i] = ['count' => $count, 'percentage' => $percentage];
                    }
                @endphp

                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100 mb-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-3xl font-serif font-medium text-ink-900 mb-2">Avis clients</h2>
                            <p class="text-ink-500">
                                {{ $stats['total_reviews'] }} avis • Note moyenne:
                                <span class="font-bold text-ochre-500 inline-flex items-center gap-1">
                                    {{ number_format($stats['rating'], 1) }}/5
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="bg-cream rounded-lg p-6">
                        <h3 class="font-bold text-ink-900 mb-4">Répartition des notes</h3>
                        <div class="space-y-3">
                            @foreach($reviewStats as $star => $data)
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-1 w-20">
                                        <span class="font-bold text-ink-900">{{ $star }}</span>
                                        <x-app-icon name="star" class="w-4 h-4 inline-block text-ochre-500" />
                                    </div>
                                    <div class="flex-1 bg-ink-100/30 rounded-full h-3 overflow-hidden">
                                        <div class="bg-ochre-500 h-full transition-all duration-500"
                                             style="width: {{ $data['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-ink-500 w-16 text-right">
                                        {{ $data['count'] }} avis
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Liste des avis --}}
                <div class="space-y-6">
                    @forelse($reviews as $review)
                        <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 hover:shadow-sm transition">
                            <div class="flex items-start gap-4">
                                {{-- Avatar --}}
                                <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                                     alt="{{ $review->reviewer->name }}"
                                     class="w-16 h-16 rounded-full border-4 border-ink-100 flex-shrink-0">

                                <div class="flex-1 min-w-0">
                                    {{-- En-tête --}}
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-bold text-ink-900 text-lg">{{ $review->reviewer->name }}</h3>
                                            @if($review->order && $review->order->service)
                                                <p class="text-sm text-ink-500">{{ $review->order->service->title }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="flex items-center gap-1 mb-1">
                                                <x-star-rating :rating="$review->rating" class="w-5 h-5" />
                                                <span class="font-bold text-ink-900">{{ $review->rating }}</span>
                                            </div>
                                            <p class="text-xs text-ink-400">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>

                                    {{-- Notes détaillées (compactes) --}}
                                    <div class="flex gap-4 mb-4 text-sm">
                                        <div class="flex items-center gap-1">
                                            <span class="text-ink-500">Qualité:</span>
                                            <x-star-rating :rating="$review->quality_rating" class="w-3.5 h-3.5" />
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-ink-500">Communication:</span>
                                            <x-star-rating :rating="$review->communication_rating" class="w-3.5 h-3.5" />
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-ink-500">Délais:</span>
                                            <x-star-rating :rating="$review->timeliness_rating" class="w-3.5 h-3.5" />
                                        </div>
                                    </div>

                                    {{-- Commentaire --}}
                                    @if($review->comment)
                                        <div class="bg-cream p-4 rounded-xl mb-3">
                                            <p class="text-ink-700 text-sm leading-relaxed">
                                                {{ $review->comment }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Action --}}
                                    <a href="{{ route('reviews.show', $review) }}"
                                       class="text-ink-900 hover:text-terracotta-700 font-bold text-sm inline-flex items-center gap-1 transition">
                                        Voir l'avis complet
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 bg-cream-50 rounded-xl border border-ink-100">
                            <div class="w-20 h-20 rounded-full bg-ochre-500/15 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-ochre-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-xl text-ink-400 mb-2">Aucun avis pour le moment</p>
                            <p class="text-ink-300">Les premiers avis apparaîtront ici</p>
                        </div>
                    @endforelse
                </div>

                @if(isset($reviews) && $reviews->hasPages())
                    <div class="mt-8">
                        {{ $reviews->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Onglet À propos --}}
        @if($activeTab === 'about')
            <div class="max-w-4xl mx-auto">
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100 mb-6">
                    <h2 class="text-2xl font-serif font-medium mb-6">À propos de {{ $prestataire->name }}</h2>

                    @if($prestataire->bio)
                        <div class="prose prose-lg max-w-none mb-6">
                            {!! nl2br(e($prestataire->bio)) !!}
                        </div>
                    @else
                        <p class="text-ink-500 italic">Ce prestataire n'a pas encore ajouté de description.</p>
                    @endif

                    <div class="grid md:grid-cols-2 gap-6 mt-8 pt-8 border-t border-ink-100">
                        <div>
                            <h3 class="font-bold text-ink-900 mb-3">Informations</h3>
                            <ul class="space-y-2 text-ink-700">
                                <li><strong>Ville:</strong> {{ $prestataire->city ?? 'Non spécifiée' }}</li>
                                <li><strong>Niveau:</strong>
                                    @if($prestataire->level === 'expert')
                                        <span class="text-ochre-600 font-bold inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> Expert</span>
                                    @elseif($prestataire->level === 'confirme' || $prestataire->level === 'professionnel')
                                        <span class="text-forest-700 font-bold inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Confirmé</span>
                                    @else
                                        <span class="text-ink-500">Nouveau</span>
                                    @endif
                                </li>
                                <li><strong>Membre depuis:</strong> {{ $prestataire->created_at->format('F Y') }}</li>
                                @if($prestataire->languages)
                                    <li><strong>Langues:</strong> {{ is_array($prestataire->languages) ? implode(', ', $prestataire->languages) : $prestataire->languages }}</li>
                                @endif
                            </ul>
                        </div>

                        @if($prestataire->service_areas && count($prestataire->service_areas) > 0)
                        <div>
                            <h3 class="font-bold text-ink-900 mb-3">Zones d'intervention</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($prestataire->service_areas as $area)
                                    <span class="bg-terracotta-50 text-terracotta-700 px-3 py-1 rounded-full text-sm flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $area }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>