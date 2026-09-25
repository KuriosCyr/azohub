<div>
    {{-- Header --}}
    <div class="bg-ink-900 text-cream-50">
        <div class="container mx-auto px-4 pt-10 pb-16">
            <div class="flex items-start justify-between mb-8 flex-wrap gap-4">
                <div>
                    <p class="text-cream-100/60 text-sm font-medium mb-1">Tableau de bord</p>
                    <h1 class="text-3xl font-serif font-medium">Bonjour, {{ Str::before(Auth::user()->name, ' ') }}</h1>
                    <p class="text-cream-100/65 text-sm mt-2">Voici où en est votre activité aujourd'hui.</p>
                </div>
                @if($hasFreeServiceSlot)
                    <a href="{{ route('prestataire.services.create') }}"
                       class="inline-flex items-center gap-2 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-5 py-2.5 rounded-lg transition text-sm shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Nouveau service
                    </a>
                @else
                    <span class="inline-flex items-center gap-2 bg-cream-50/10 text-cream-100/50 font-bold px-5 py-2.5 rounded-lg text-sm cursor-not-allowed"
                          title="Limite de services atteinte ({{ $slotsUsed }}/{{ $slotsMax }})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Nouveau service
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-10 pb-10 flex flex-col gap-6">

        {{-- A FAIRE MAINTENANT --}}
        <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-lg px-6">
            <div class="flex items-baseline justify-between pt-4 pb-3">
                <h2 class="text-base font-bold text-ink-900">À faire maintenant</h2>
                <span class="text-xs text-ink-400">
                    {{ count($todoItems) > 0 ? count($todoItems) . ' élément(s) demandent votre attention' : '' }}
                </span>
            </div>

            @forelse($todoItems as $item)
                @php
                    [$chipBg, $iconColor, $icon] = match($item['type']) {
                        'delivery' => ['bg-ochre-500/15', '#CA8A04', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'revision' => ['bg-clay-500/15', '#0EA5E9', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                        'messages' => ['bg-terracotta-50', '#2563EB', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.93 7.93 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                        'rejected' => ['bg-red-100', '#DC2626', 'M6 18L18 6M6 6l12 12'],
                        'identity' => ['bg-forest-600/10', '#15803D', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        'trial' => ['bg-ochre-500/15', '#CA8A04', 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z'],
                    };
                    $href = match($item['type']) {
                        'delivery', 'revision' => route('prestataire.orders.index'),
                        'messages' => route('conversations.index'),
                        'rejected' => route('prestataire.services.index', ['status' => 'pending']),
                        'identity' => route('profile.edit'),
                        'trial' => route('prestataire.subscription'),
                    };
                @endphp
                <a href="{{ $href }}" class="flex items-center gap-3.5 py-3.5 border-t border-ink-100 hover:bg-ink-100/20 -mx-2 px-2 rounded-lg transition">
                    <div class="w-10 h-10 rounded-lg {{ $chipBg }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="{{ $iconColor }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-ink-900">{{ $item['title'] }}</p>
                        @if($item['subtitle'])
                            <p class="text-xs text-ink-400 mt-0.5 truncate">{{ $item['subtitle'] }}</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-ink-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <div class="flex items-center gap-3 py-4 border-t border-ink-100">
                    <div class="w-10 h-10 rounded-lg bg-forest-600/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-[18px] h-[18px] text-forest-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-ink-700">Rien à signaler, tout est à jour !</p>
                </div>
            @endforelse
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('prestataire.wallet') }}" class="bg-cream-50 rounded-lg p-5 border border-ink-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-ochre-500/15 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-ochre-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="text-ink-300 text-xs font-semibold uppercase tracking-wide">Portefeuille</span>
                </div>
                <p class="text-3xl font-bold text-ink-900">{{ number_format($stats['wallet_balance'], 0, ',', ' ') }}</p>
                <p class="text-ink-400 text-xs mt-0.5">FCFA disponible</p>
                @if($stats['pending_earnings'] > 0)
                    <p class="text-ink-300 text-[11px] mt-1.5">+ {{ number_format($stats['pending_earnings'], 0, ',', ' ') }} FCFA en attente (escrow)</p>
                @endif
            </a>

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

        {{-- MON ACTIVITE --}}
        <div class="bg-cream-50 rounded-xl border border-ink-100 shadow-sm overflow-hidden">
            @php
                $levelTheme = match(Auth::user()->level) {
                    'expert' => ['from' => '#7C3800', 'to' => '#CA8A04', 'icon' => '#FDE68A'],
                    'confirme' => ['from' => '#0A1F42', 'to' => '#1D4ED8', 'icon' => '#93C5FD'],
                    default => ['from' => '#0A1F42', 'to' => '#0F2A5C', 'icon' => '#94A3B8'],
                };
            @endphp
            <div class="p-6 text-cream-50" style="background: linear-gradient(135deg, {{ $levelTheme['from'] }} 0%, {{ $levelTheme['to'] }} 100%);">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-white/10 backdrop-blur-sm">
                            <svg class="w-7 h-7" style="color: {{ $levelTheme['icon'] }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-cream-100/60 uppercase tracking-wide mb-0.5">Votre niveau</p>
                            <p class="text-2xl font-serif font-medium">{{ Auth::user()->level_label }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-cream-100/60 uppercase tracking-wide mb-0.5">Commission Azohub</p>
                        <p class="text-2xl font-bold" style="color: {{ $levelTheme['icon'] }}">{{ number_format(Auth::user()->commissionRate() * 100, 0, ',', ' ') }}%</p>
                    </div>
                </div>

                @if(Auth::user()->level !== 'expert')
                    @php
                        $nextLevel = Auth::user()->level === 'nouveau'
                            ? ['orders' => 15, 'rating' => 4.3, 'label' => 'Confirmé']
                            : ['orders' => 50, 'rating' => 4.7, 'label' => 'Expert'];
                        $ordersProgress = $nextLevel['orders'] > 0 ? min(100, ($stats['completed_orders'] / $nextLevel['orders']) * 100) : 100;
                    @endphp
                    <div class="mt-5 pt-4 border-t border-white/15">
                        <div class="flex justify-between text-xs text-cream-100/70 mb-1.5">
                            <span>Progression vers <strong class="text-cream-50">{{ $nextLevel['label'] }}</strong> (commission réduite)</span>
                            <span>{{ $stats['completed_orders'] }}/{{ $nextLevel['orders'] }} commandes · note ≥ {{ $nextLevel['rating'] }}</span>
                        </div>
                        <div class="h-2 bg-white/15 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $ordersProgress }}%; background: {{ $levelTheme['icon'] }};"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-ink-100">
                {{-- Places de services --}}
                <div class="p-5">
                    <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                        <x-app-icon name="box" class="w-3.5 h-3.5" /> Places de services
                    </p>
                    @if($slotsMax === null)
                        <p class="text-sm font-bold text-ink-900">{{ $slotsUsed }} service(s) · illimité</p>
                    @else
                        <p class="text-sm font-bold text-ink-900 mb-1.5">{{ $slotsUsed }} / {{ $slotsMax }} utilisées</p>
                        <div class="h-1.5 bg-ink-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $slotsUsed >= $slotsMax ? 'bg-red-500' : 'bg-ochre-500' }}"
                                 style="width: {{ min(100, ($slotsUsed / max($slotsMax, 1)) * 100) }}%"></div>
                        </div>
                    @endif
                </div>

                {{-- Abonnement --}}
                <div class="p-5">
                    <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                        <x-app-icon name="sparkles" class="w-3.5 h-3.5" /> Abonnement
                    </p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-terracotta-50 text-terracotta-700">
                            Plan {{ $currentPlan->name ?? 'Gratuit' }}
                        </span>
                        @if($activeSubscription && (float) ($currentPlan->price ?? 0) > 0)
                            <span class="text-xs text-ink-400">expire dans {{ max(0, (int) floor(now()->diffInDays($activeSubscription->ends_at, false))) }} j</span>
                        @endif
                    </div>
                    <a href="{{ route('prestataire.subscription') }}" class="text-xs font-bold text-terracotta-600 hover:text-terracotta-700 mt-1.5 inline-block">
                        {{ (float) ($currentPlan->price ?? 0) > 0 ? 'Gérer mon abonnement →' : 'Passer à un plan supérieur →' }}
                    </a>
                </div>

                {{-- Offre de bienvenue --}}
                <div class="p-5">
                    <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                        <x-app-icon name="rocket" class="w-3.5 h-3.5" /> Offre de bienvenue
                    </p>
                    @if($welcomePromoEndsAt)
                        @php
                            $promoRate = (float) config('services.azohub.welcome_promo.rate');
                            // La commission réellement appliquée est le MEILLEUR taux parmi niveau/plan/offre de
                            // bienvenue (cf. User::commissionRate()) — si le plan actuel fait déjà mieux que
                            // l'offre de bienvenue, celle-ci ne sert plus à rien pour l'instant : dire "active"
                            // dans ce cas induit en erreur (elle ne l'est pas, elle est juste "de réserve").
                            $promoIsApplied = round(Auth::user()->commissionRate() * 100, 2) === round($promoRate, 2);
                        @endphp
                        @if($promoIsApplied)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-ochre-500/15 text-ochre-600">Commission {{ number_format($promoRate, 0) }}% active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-ink-100 text-ink-500">Remplacée par votre plan ({{ number_format(Auth::user()->commissionRate() * 100, 0) }}%)</span>
                        @endif
                        <p class="text-xs text-ink-400 mt-1.5">Jusqu'au {{ $welcomePromoEndsAt->translatedFormat('d M Y') }}</p>
                    @else
                        <p class="text-sm text-ink-400">Terminée ou non applicable</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- LIENS RAPIDES --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('prestataire.services.index') }}" class="bg-cream-50 rounded-lg p-4 border border-ink-100 shadow-sm hover:shadow-md transition flex items-center gap-3">
                <div class="w-10 h-10 bg-terracotta-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-ink-900">Mes services</p>
                    <p class="text-xs text-ink-400">{{ $stats['services_count'] }} service(s)</p>
                </div>
                <svg class="w-3.5 h-3.5 text-ink-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('prestataire.orders.index') }}" class="bg-cream-50 rounded-lg p-4 border border-ink-100 shadow-sm hover:shadow-md transition flex items-center gap-3">
                <div class="w-10 h-10 bg-ochre-500/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-ochre-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-ink-900">Commandes</p>
                    <p class="text-xs text-ink-400">{{ $stats['total_orders'] }} au total</p>
                </div>
                <svg class="w-3.5 h-3.5 text-ink-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('prestataire.reviews.index') }}" class="bg-cream-50 rounded-lg p-4 border border-ink-100 shadow-sm hover:shadow-md transition flex items-center gap-3">
                <div class="w-10 h-10 bg-forest-600/10 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-forest-700" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-ink-900">Avis</p>
                    <p class="text-xs text-ink-400">{{ $stats['total_reviews'] }} avis</p>
                </div>
                <svg class="w-3.5 h-3.5 text-ink-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('conversations.index') }}" class="bg-cream-50 rounded-lg p-4 border border-ink-100 shadow-sm hover:shadow-md transition flex items-center gap-3 relative">
                <div class="w-10 h-10 bg-terracotta-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.93 7.93 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-ink-900">Messages</p>
                    <p class="text-xs text-ink-400">{{ $stats['unread_messages'] }} non lu(s)</p>
                </div>
                @if($stats['unread_messages'] > 0)
                    <span class="w-2 h-2 rounded-full bg-red-600 absolute top-3.5 right-3.5"></span>
                @endif
            </a>
        </div>

        {{-- COMMANDES ACTIVES + REVENUS --}}
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-ink-900">Commandes actives</h2>
                    <span class="bg-ochre-500/15 text-ink-900 px-3 py-1 rounded-full font-bold text-xs">
                        {{ $activeOrders->count() }}
                    </span>
                </div>

                <div class="divide-y divide-ink-100">
                    @forelse($activeOrders->take(5) as $order)
                        <a href="{{ route('orders.show', $order) }}"
                           class="block px-6 py-4 hover:bg-ink-100/30 transition">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 min-w-0 mr-3">
                                    <p class="font-bold text-ink-900 text-sm truncate">{{ $order->display_title }}</p>
                                    <p class="text-xs text-ink-400">Client : {{ $order->client->name }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0
                                    {{ $order->status === 'paid' ? 'bg-terracotta-50 text-terracotta-700' : 'bg-ochre-500/15 text-ink-900' }}">
                                    {{ $order->status === 'paid' ? 'Nouvelle' : 'En cours' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-ink-400">{{ $order->created_at->diffForHumans() }}</span>
                                <span class="font-bold text-ink-900">{{ number_format($order->prestataire_amount, 0, ',', ' ') }} FCFA</span>
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

            <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-bold text-ink-900">Revenus des 30 derniers jours</h2>
                        <p class="text-xs text-ink-400 mt-0.5">Évolution de vos gains</p>
                    </div>
                    <div class="w-9 h-9 bg-terracotta-50 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div style="height: 220px; position: relative;">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- AVIS RECENTS --}}
        <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-ink-900">Avis récents</h2>
                <a href="{{ route('prestataire.reviews.index') }}" class="text-xs font-bold text-terracotta-600 hover:text-terracotta-700">Tout voir ({{ $stats['total_reviews'] }})</a>
            </div>

            @if($recentReviews->isEmpty())
                <p class="text-ink-500 text-sm">Aucun avis pour le moment.</p>
            @else
                <div class="grid md:grid-cols-3 gap-4">
                    @foreach($recentReviews as $review)
                        <div class="bg-cream rounded-xl p-4">
                            <div class="flex gap-0.5 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-ochre-500' : 'text-ink-200' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                            @if($review->comment)
                                <p class="text-ink-700 text-xs leading-relaxed mb-2.5">« {{ Str::limit($review->comment, 110) }} »</p>
                            @endif
                            <p class="text-ink-400 text-[11px] font-bold">{{ $review->reviewer->name }} @if($review->order->service) · {{ $review->order->service->title }} @endif</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
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
                        labels: @json($monthlyEarnings['labels']),
                        datasets: [{
                            label: 'Revenus (FCFA)',
                            data: @json($monthlyEarnings['data']),
                            borderColor: 'rgb(30, 58, 138)',
                            backgroundColor: 'rgba(30, 58, 138, 0.06)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
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
                                ticks: { font: { size: 11 }, maxTicksLimit: 8 }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>
