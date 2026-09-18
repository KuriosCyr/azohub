<div>
    {{-- Header avec stats --}}
    <div class="bg-ink-900 text-cream-50">
        <div class="container mx-auto px-4 py-10">
            <div class="flex items-start justify-between mb-8">
                <div>
                    <p class="text-cream-100/60 text-sm font-medium mb-1">Tableau de bord</p>
                    <h1 class="text-3xl font-serif font-medium">Bienvenue, {{ Auth::user()->name }}</h1>
                </div>
                <a href="{{ route('prestataire.services.create') }}"
                   class="inline-flex items-center gap-2 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-5 py-2.5 rounded-lg transition text-sm shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nouveau service
                </a>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Portefeuille --}}
                <div class="bg-cream-50/10 backdrop-blur-sm rounded-lg p-5 border border-cream-50/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-cream-50/10 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-ochre-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <span class="text-cream-100/60 text-xs font-semibold uppercase tracking-wide">Portefeuille</span>
                    </div>
                    <p class="text-3xl font-bold text-cream-50">{{ number_format($stats['wallet_balance'], 0) }}</p>
                    <p class="text-cream-100/60 text-xs mt-0.5">FCFA disponible</p>
                </div>

                {{-- Commandes en cours --}}
                <div class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-ochre-500/15 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-ochre-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-ink-300 text-xs font-semibold uppercase tracking-wide">En cours</span>
                    </div>
                    <p class="text-3xl font-bold text-ink-900">{{ $stats['pending_orders'] + $stats['in_progress_orders'] }}</p>
                    <p class="text-ink-400 text-xs mt-0.5">Commandes actives</p>
                </div>

                {{-- Terminées --}}
                <div class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-forest-600/10 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-ink-300 text-xs font-semibold uppercase tracking-wide">Terminées</span>
                    </div>
                    <p class="text-3xl font-bold text-ink-900">{{ $stats['completed_orders'] }}</p>
                    <p class="text-ink-400 text-xs mt-0.5">Missions réussies</p>
                </div>

                {{-- Note moyenne --}}
                <div class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-ochre-500/15 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-ochre-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <span class="text-ink-300 text-xs font-semibold uppercase tracking-wide">Note</span>
                    </div>
                    <p class="text-3xl font-bold text-ink-900">{{ number_format($stats['rating'], 1) }}</p>
                    <p class="text-ink-400 text-xs mt-0.5">{{ $stats['total_reviews'] }} avis</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenu principal --}}
    <div class="container mx-auto px-4 py-8">
        {{-- Niveau & Commission --}}
        <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center
                        {{ Auth::user()->level === 'expert' ? 'bg-ochre-500/15' : (Auth::user()->level === 'confirme' ? 'bg-terracotta-50' : 'bg-ink-100/40') }}">
                        <svg class="w-6 h-6 {{ Auth::user()->level === 'expert' ? 'text-ochre-600' : (Auth::user()->level === 'confirme' ? 'text-terracotta-600' : 'text-ink-400') }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-0.5">Votre niveau</p>
                        <p class="text-lg font-bold text-ink-900">{{ ['nouveau' => 'Nouveau', 'confirme' => 'Confirmé', 'expert' => 'Expert'][Auth::user()->level] ?? ucfirst(Auth::user()->level) }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-0.5">Commission Azohub</p>
                    <p class="text-lg font-bold text-terracotta-600">{{ number_format(Auth::user()->commissionRate() * 100, 0) }}%</p>
                </div>
            </div>

            @if(Auth::user()->level !== 'expert')
                @php
                    $nextLevel = Auth::user()->level === 'nouveau'
                        ? ['orders' => 15, 'rating' => 4.3, 'label' => 'Confirmé']
                        : ['orders' => 50, 'rating' => 4.7, 'label' => 'Expert'];
                    $ordersProgress = $nextLevel['orders'] > 0 ? min(100, ($stats['completed_orders'] / $nextLevel['orders']) * 100) : 100;
                @endphp
                <div class="mt-4 pt-4 border-t border-ink-100">
                    <div class="flex justify-between text-xs text-ink-500 mb-1.5">
                        <span>Progression vers <strong class="text-ink-700">{{ $nextLevel['label'] }}</strong> (commission réduite)</span>
                        <span>{{ $stats['completed_orders'] }}/{{ $nextLevel['orders'] }} commandes · note ≥ {{ $nextLevel['rating'] }}</span>
                    </div>
                    <div class="h-2 bg-ink-100 rounded-full overflow-hidden">
                        <div class="h-full bg-terracotta-600 rounded-full" style="width: {{ $ordersProgress }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Onglets --}}
        <div class="bg-cream-50 rounded-lg shadow-sm border border-ink-100 mb-6 overflow-hidden">
            <div class="flex">
                @foreach(['overview' => 'Vue d\'ensemble', 'services' => 'Mes services (' . $stats['services_count'] . ')', 'orders' => 'Commandes', 'reviews' => 'Avis (' . $stats['total_reviews'] . ')'] as $tab => $label)
                    <button wire:click="setTab('{{ $tab }}')"
                            class="flex-1 px-4 py-3.5 font-bold text-sm transition border-b-2
                            {{ $activeTab === $tab ? 'border-terracotta-600 text-terracotta-700 bg-terracotta-50/50' : 'border-transparent text-ink-500 hover:text-ink-700 hover:bg-ink-100/30' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Vue d'ensemble --}}
        @if($activeTab === 'overview')
            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Commandes actives --}}
                <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-ink-900">Commandes actives</h2>
                        <span class="bg-ochre-500/15 text-ink-900 px-3 py-1 rounded-full font-bold text-xs">
                            {{ $activeOrders->count() }}
                        </span>
                    </div>

                    <div class="divide-y divide-ink-100">
                        @forelse($activeOrders as $order)
                            <a href="{{ route('orders.show', $order) }}"
                               class="block px-6 py-4 hover:bg-ink-100/30 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 min-w-0 mr-3">
                                        <p class="font-bold text-ink-900 text-sm truncate">{{ $order->service->title }}</p>
                                        <p class="text-xs text-ink-400">Client : {{ $order->client->name }}</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0
                                        {{ $order->status === 'paid' ? 'bg-terracotta-50 text-terracotta-700' : 'bg-ochre-500/15 text-ink-900' }}">
                                        {{ $order->status === 'paid' ? 'Nouvelle' : 'En cours' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-ink-400">{{ $order->created_at->diffForHumans() }}</span>
                                    <span class="font-bold text-ink-900">{{ number_format($order->prestataire_amount, 0) }} FCFA</span>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-ink-500 text-sm font-medium mb-3">Aucune commande active</p>
                                <a href="{{ route('prestataire.services.index') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold text-sm">
                                    Gérer mes services →
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Avis récents --}}
                <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-ink-900">Avis récents</h2>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="font-bold text-ink-900 text-sm">{{ number_format($stats['rating'], 1) }}</span>
                        </div>
                    </div>

                    <div class="divide-y divide-ink-100">
                        @forelse($recentReviews as $review)
                            <div class="px-6 py-4">
                                <div class="flex items-center gap-3 mb-2">
                                    <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                                         alt="{{ $review->reviewer->name }}"
                                         class="w-9 h-9 rounded-full flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-ink-900 text-sm">{{ $review->reviewer->name }}</p>
                                        <div class="flex items-center gap-2">
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-ochre-500' : 'text-ink-200' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-ink-400">{{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="text-ink-500 text-xs bg-cream rounded-xl px-3 py-2">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-full bg-ochre-500/15 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <p class="text-ink-500 text-sm font-medium">Aucun avis pour le moment</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Graphique revenus --}}
            <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm p-6 mt-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-bold text-ink-900">Revenus des 7 derniers jours</h2>
                        <p class="text-xs text-ink-400 mt-0.5">Évolution de vos gains</p>
                    </div>
                    <div class="w-9 h-9 bg-terracotta-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div style="height: 250px; position: relative;">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Mes services --}}
        @if($activeTab === 'services')
            <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-ink-900">Mes services</h2>
                    <a href="{{ route('prestataire.services.create') }}"
                       class="inline-flex items-center gap-2 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-4 py-2 rounded-lg transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Nouveau
                    </a>
                </div>

                <div class="divide-y divide-ink-100">
                    @forelse($recentServices as $service)
                        <div class="px-6 py-5 hover:bg-ink-100/30 transition">
                            <div class="flex gap-4">
                                <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0">
                                    <x-service-cover :service="$service" class="w-full h-full object-cover" />
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-3 mb-2">
                                        <div class="min-w-0">
                                            <span class="inline-block px-2.5 py-0.5 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full mb-1">
                                                {{ $service->category->name }}
                                            </span>
                                            <h3 class="font-bold text-ink-900 text-sm truncate">{{ $service->title }}</h3>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0 {{ $service->is_active ? 'bg-forest-600/10 text-forest-700' : 'bg-ink-100 text-ink-500' }}">
                                            {{ $service->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </div>

                                    <div class="flex gap-5 text-xs mb-3">
                                        <div>
                                            <p class="text-ink-300">Prix</p>
                                            <p class="font-bold text-ink-900">{{ number_format($service->price, 0) }} FCFA</p>
                                        </div>
                                        <div>
                                            <p class="text-ink-300">Commandes</p>
                                            <p class="font-bold text-ink-900">{{ $service->total_orders }}</p>
                                        </div>
                                        <div>
                                            <p class="text-ink-300">Note</p>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                <span class="font-bold text-ink-900">{{ number_format($service->rating, 1) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('services.show', $service->slug) }}"
                                           class="inline-flex items-center gap-1.5 bg-ink-100 hover:bg-ink-200 text-ink-700 font-bold px-3.5 py-1.5 rounded-lg transition text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Voir
                                        </a>
                                        <a href="{{ route('prestataire.services.edit', $service) }}"
                                           class="inline-flex items-center gap-1.5 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-3.5 py-1.5 rounded-lg transition text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Modifier
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <div class="w-16 h-16 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-ink-900 mb-2">Aucun service créé</h3>
                            <p class="text-ink-500 text-sm mb-6">Créez votre premier service pour attirer des clients !</p>
                            <a href="{{ route('prestataire.services.create') }}"
                               class="inline-flex items-center gap-2 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                Créer mon premier service
                            </a>
                        </div>
                    @endforelse
                </div>

                @if($recentServices->count() > 0)
                    <div class="px-6 py-4 border-t border-ink-100 text-center">
                        <a href="{{ route('prestataire.services.index') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold text-sm">
                            Voir tous mes services ({{ $stats['services_count'] }}) →
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Commandes --}}
        @if($activeTab === 'orders')
            <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-ink-100">
                    <h2 class="text-base font-bold text-ink-900">Toutes mes commandes</h2>
                </div>

                <div class="divide-y divide-ink-100">
                    @forelse($activeOrders->merge($recentCompletedOrders) as $order)
                        <a href="{{ route('orders.show', $order) }}"
                           class="block px-6 py-5 hover:bg-ink-100/30 transition">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2.5 mb-1">
                                        <span class="font-bold text-ink-400 text-xs">#{{ $order->order_number }}</span>
                                        @php
                                            $statusMap = [
                                                'paid'        => ['bg-terracotta-50 text-terracotta-700', 'Nouvelle'],
                                                'in_progress' => ['bg-ochre-500/15 text-ink-900', 'En cours'],
                                                'delivered'   => ['bg-clay-500/15 text-ink-900', 'Livrée'],
                                                'completed'   => ['bg-forest-600/10 text-forest-700', 'Terminée'],
                                                'cancelled'   => ['bg-red-100 text-red-800', 'Annulée'],
                                            ];
                                            [$badgeClass, $badgeLabel] = $statusMap[$order->status] ?? ['bg-ink-100 text-ink-700', ucfirst($order->status)];
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                    </div>
                                    <p class="font-bold text-ink-900 text-sm truncate">{{ $order->service->title }}</p>
                                    <p class="text-xs text-ink-400 mt-0.5">Client : {{ $order->client->name }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xl font-bold text-ink-900">{{ number_format($order->prestataire_amount, 0) }} F</p>
                                    <p class="text-xs text-ink-400 mt-0.5">{{ $order->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <div class="w-16 h-16 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                            </div>
                            <p class="text-ink-500 font-medium">Aucune commande pour le moment</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Avis --}}
        @if($activeTab === 'reviews')
            <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-ink-900">Tous mes avis</h2>
                    <div class="flex items-center gap-1.5 bg-ochre-500/15 px-3 py-1.5 rounded-full">
                        <svg class="w-4 h-4 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span class="font-bold text-ink-900 text-sm">{{ number_format($stats['rating'], 1) }}/5</span>
                    </div>
                </div>

                <div class="divide-y divide-ink-100">
                    @forelse($recentReviews as $review)
                        <div class="px-6 py-5">
                            <div class="flex gap-4">
                                <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                                     alt="{{ $review->reviewer->name }}"
                                     class="w-11 h-11 rounded-full flex-shrink-0">
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-bold text-ink-900 text-sm">{{ $review->reviewer->name }}</p>
                                            <p class="text-xs text-ink-400">{{ $review->order->service->title }}</p>
                                        </div>
                                        <span class="text-xs text-ink-400">{{ $review->created_at->format('d/m/Y') }}</span>
                                    </div>

                                    <div class="flex items-center gap-4 mb-3">
                                        <div>
                                            <p class="text-xs text-ink-400 mb-1">Note globale</p>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-ochre-500' : 'text-ink-200' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-ink-400">Qualité : </span>
                                            <span class="font-bold text-ink-700">{{ $review->quality_rating }}/5</span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-ink-400">Communication : </span>
                                            <span class="font-bold text-ink-700">{{ $review->communication_rating }}/5</span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-ink-400">Délais : </span>
                                            <span class="font-bold text-ink-700">{{ $review->timeliness_rating }}/5</span>
                                        </div>
                                    </div>

                                    @if($review->comment)
                                        <p class="text-ink-500 text-sm bg-cream p-3 rounded-xl">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <div class="w-16 h-16 rounded-full bg-ochre-500/15 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <p class="text-ink-500 font-medium">Aucun avis pour le moment</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('earningsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($weeklyEarnings['labels']),
                        datasets: [{
                            label: 'Revenus (FCFA)',
                            data: @json($weeklyEarnings['data']),
                            borderColor: 'rgb(30, 58, 138)',
                            backgroundColor: 'rgba(30, 58, 138, 0.06)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(30, 58, 138)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(30, 58, 138, 0.95)',
                                padding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenus: ' + context.parsed.y.toLocaleString() + ' FCFA';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.04)' },
                                ticks: {
                                    callback: function(value) { return value.toLocaleString() + ' F'; },
                                    font: { size: 11 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>
