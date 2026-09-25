<div class="min-h-screen bg-cream">
    {{-- Header --}}
    <div class="bg-cream-50 border-b border-ink-100">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-ink-900">
                        Bonjour, {{ Auth::user()->name }}
                    </h1>
                    <p class="text-ink-400 text-sm mt-0.5">Gérez vos commandes et découvrez de nouveaux services</p>
                </div>
                <a href="{{ route('services.index') }}"
                   class="inline-flex items-center gap-2 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-5 py-2.5 rounded-lg transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Explorer les services
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        {{-- Statistiques --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Total Commandes --}}
            <div class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-terracotta-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-ink-300 uppercase tracking-wide">Total</span>
                </div>
                <p class="text-3xl font-bold text-ink-900">{{ $stats['total_orders'] }}</p>
                <p class="text-xs text-ink-400 mt-0.5">Commandes passées</p>
            </div>

            {{-- Commandes en cours --}}
            <div class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-ochre-500/15 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-ochre-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-ink-300 uppercase tracking-wide">En cours</span>
                </div>
                <p class="text-3xl font-bold text-ink-900">{{ $stats['pending_orders'] }}</p>
                <p class="text-xs text-ink-400 mt-0.5">Commandes actives</p>
            </div>

            {{-- Commandes terminées --}}
            <div class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-forest-600/10 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-ink-300 uppercase tracking-wide">Terminées</span>
                </div>
                <p class="text-3xl font-bold text-ink-900">{{ $stats['completed_orders'] }}</p>
                <p class="text-xs text-ink-400 mt-0.5">Commandes validées</p>
            </div>

            {{-- Dépenses totales --}}
            <div class="bg-ink-900 rounded-lg p-5 text-cream-50">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-cream-50/10 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-cream-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-cream-100/60 uppercase tracking-wide">Total</span>
                </div>
                <p class="text-2xl font-bold">{{ number_format($stats['total_spent'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-cream-100/60 mt-0.5">Dépenses totales</p>
            </div>
        </div>

        {{-- Actions requises --}}
        @if($stats['awaiting_validation'] > 0)
            <div class="bg-ochre-500/10 border border-ochre-500/30 rounded-lg p-5 mb-8">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-ochre-500/15 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-ochre-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-ink-900 mb-1">
                            {{ $stats['awaiting_validation'] }} commande(s) en attente de validation
                        </h3>
                        <p class="text-ink-700 text-sm mb-4">
                            Des prestataires ont livré leur travail. Validez pour débloquer les paiements.
                        </p>
                        <div class="space-y-2">
                            @foreach($actionRequiredOrders as $order)
                                <div class="bg-cream-50 rounded-xl p-4 flex items-center justify-between border border-ochre-500/20">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $order->prestataire->avatar ? Storage::url($order->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($order->prestataire->name) }}"
                                             alt="{{ $order->prestataire->name }}"
                                             class="w-10 h-10 rounded-full border-2 border-ochre-500/20">
                                        <div>
                                            <p class="font-bold text-ink-900 text-sm">{{ $order->display_title }}</p>
                                            <p class="text-xs text-ink-400">Par {{ $order->prestataire->name }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('orders.show', $order) }}"
                                       class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-4 py-2 rounded-lg transition text-sm">
                                        Valider
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Colonne principale --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Commandes récentes --}}
                <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h2 class="text-lg font-bold text-ink-900">Commandes récentes</h2>
                        </div>
                        <a href="{{ route('client.orders.index') }}" class="text-terracotta-600 hover:text-terracotta-700 font-semibold text-xs">
                            Voir tout →
                        </a>
                    </div>

                    <div class="divide-y divide-ink-100">
                        @forelse($recentOrders as $order)
                            <div class="px-6 py-4 hover:bg-ink-100/30 transition">
                                <div class="flex items-start gap-4">
                                    <img src="{{ $order->prestataire->avatar ? Storage::url($order->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($order->prestataire->name) }}"
                                         alt="{{ $order->prestataire->name }}"
                                         class="w-12 h-12 rounded-xl border border-ink-100 flex-shrink-0">

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <div class="min-w-0">
                                                <h3 class="font-bold text-ink-900 text-sm truncate">{{ $order->display_title }}</h3>
                                                <p class="text-xs text-ink-400">Par {{ $order->prestataire->name }}</p>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap flex-shrink-0 {{ $order->status_badge_class }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4 text-xs text-ink-300 mb-2">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                {{ $order->display_category ?? 'Offre personnalisée' }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $order->created_at->format('d/m/Y') }}
                                            </span>
                                            <span class="font-bold text-ink-900">{{ number_format($order->total_charged, 0, ',', ' ') }} F</span>
                                        </div>

                                        <a href="{{ route('orders.show', $order) }}"
                                           class="inline-flex items-center gap-1 text-terracotta-600 hover:text-terracotta-700 font-semibold text-xs">
                                            Voir les détails
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-14 text-center">
                                <div class="w-16 h-16 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-ink-500 mb-4 font-medium">Vous n'avez pas encore de commandes</p>
                                <a href="{{ route('services.index') }}"
                                   class="inline-flex items-center gap-2 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-5 py-2.5 rounded-lg transition text-sm">
                                    Découvrir les services
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Lien vers la liste complète --}}
                @if($stats['total_orders'] > 5)
                    <a href="{{ route('client.orders.index') }}"
                       class="flex items-center justify-center gap-2 bg-cream-50 hover:bg-ink-100/30 rounded-lg border border-ink-100 shadow-sm py-4 text-sm font-bold text-ink-700 transition">
                        Voir toutes mes commandes ({{ $stats['total_orders'] }})
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                {{-- Services populaires --}}
                <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-ink-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <h3 class="font-bold text-ink-900 text-sm">Services populaires</h3>
                    </div>

                    <div class="p-5 space-y-4">
                        @foreach($recommendedServices as $service)
                            <a href="{{ route('services.show', $service->slug) }}"
                               x-data x-intersect.once="$el.classList.add('revealed')"
                               style="transition-delay: {{ $loop->index * 70 }}ms"
                               class="reveal block group">
                                <div class="flex gap-3">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0">
                                        <x-service-cover :service="$service" class="w-full h-full object-cover" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-ink-900 group-hover:text-terracotta-700 transition truncate text-sm">
                                            {{ $service->title }}
                                        </h4>
                                        <p class="text-xs text-ink-300 truncate">{{ $service->prestataire->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-sm font-bold text-ink-900">{{ number_format($service->price, 0, ',', ' ') }} F</span>
                                            @if($service->rating > 0)
                                                <span class="flex items-center gap-0.5 text-xs text-ink-300">
                                                    <svg class="w-3 h-3 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                    {{ number_format($service->rating, 1) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="px-5 py-3 bg-cream border-t border-ink-100">
                        <a href="{{ route('services.index') }}"
                           class="block text-center bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-2.5 rounded-xl transition text-sm">
                            Voir tous les services
                        </a>
                    </div>
                </div>

                {{-- Aide --}}
                <div class="bg-ink-900 rounded-lg p-5 text-cream-50">
                    <div class="w-10 h-10 bg-cream-50/10 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-cream-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold mb-1.5">Besoin d'aide ?</h3>
                    <p class="text-cream-100/60 text-xs mb-4 leading-relaxed">
                        Notre équipe support est disponible pour vous assister.
                    </p>
                    <a href="{{ route('contact') }}"
                       class="block text-center bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold py-2.5 rounded-xl transition text-sm">
                        Nous contacter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
