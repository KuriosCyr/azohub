<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">Statistiques</h1>
                <p class="text-ink-500">Plan {{ $plan->name }} — {{ $plan->slug === 'premium' ? 'statistiques complètes' : 'statistiques avancées' }}.</p>
            </div>
        </div>

        {{-- Chiffres clés --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-cream-50 rounded-xl p-5 border border-ink-100">
                <p class="text-ink-400 text-xs font-semibold uppercase tracking-wide mb-2">Vues du profil</p>
                <p class="text-3xl font-bold text-ink-900">{{ number_format($totalViews) }}</p>
                <p class="text-xs text-ink-400 mt-1">{{ $viewsThisMonth }} ce mois</p>
            </div>
            <div class="bg-cream-50 rounded-xl p-5 border border-ink-100">
                <p class="text-ink-400 text-xs font-semibold uppercase tracking-wide mb-2">Commandes</p>
                <p class="text-3xl font-bold text-ink-900">{{ number_format($totalOrders) }}</p>
                <p class="text-xs text-ink-400 mt-1">{{ $ordersThisMonth }} ce mois</p>
            </div>
            <div class="bg-cream-50 rounded-xl p-5 border border-ink-100">
                <p class="text-ink-400 text-xs font-semibold uppercase tracking-wide mb-2">Revenus (net)</p>
                <p class="text-3xl font-bold text-ink-900">{{ number_format($totalRevenue, 0, ',', ' ') }}</p>
                <p class="text-xs text-ink-400 mt-1">FCFA au total</p>
            </div>
            <div class="bg-cream-50 rounded-xl p-5 border border-ink-100">
                <p class="text-ink-400 text-xs font-semibold uppercase tracking-wide mb-2">Taux de conversion</p>
                <p class="text-3xl font-bold text-ink-900">{{ $conversionRate }}%</p>
                <p class="text-xs text-ink-400 mt-1">Vues → commandes</p>
            </div>
        </div>

        {{-- Temps de réponse --}}
        <div class="bg-cream-50 rounded-xl p-5 border border-ink-100 mb-8 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-clay-500/15 flex items-center justify-center flex-shrink-0">
                <x-app-icon name="chat" class="w-6 h-6 text-clay-600" />
            </div>
            <div>
                <p class="text-ink-400 text-xs font-semibold uppercase tracking-wide mb-1">Temps de réponse moyen</p>
                <p class="text-xl font-bold text-ink-900">{{ $responseTimeLabel }}</p>
                <p class="text-xs text-ink-400 mt-0.5">Un client qui obtient une réponse rapide commande plus souvent.</p>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                <h2 class="font-bold text-ink-900 mb-6">Vues du profil (6 derniers mois)</h2>
                @php $maxViews = max(1, max($viewsChart)); @endphp
                <div class="flex items-end justify-between gap-2 h-40">
                    @foreach($viewsChart as $i => $value)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <span class="text-xs font-semibold text-ink-500">{{ $value }}</span>
                            <div class="w-full bg-terracotta-600 rounded-t-md" style="height: {{ max(4, ($value / $maxViews) * 100) }}%"></div>
                            <span class="text-xs text-ink-400">{{ $months[$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                <h2 class="font-bold text-ink-900 mb-6">Revenus nets (6 derniers mois)</h2>
                @php $maxRevenue = max(1, max($revenueChart)); @endphp
                <div class="flex items-end justify-between gap-2 h-40">
                    @foreach($revenueChart as $i => $value)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <span class="text-xs font-semibold text-ink-500">{{ number_format($value, 0, ',', ' ') }}</span>
                            <div class="w-full bg-forest-600 rounded-t-md" style="height: {{ max(4, ($value / $maxRevenue) * 100) }}%"></div>
                            <span class="text-xs text-ink-400">{{ $months[$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                <h2 class="font-bold text-ink-900 mb-6">Messages reçus (6 derniers mois)</h2>
                @php $maxMessages = max(1, max($messagesChart)); @endphp
                <div class="flex items-end justify-between gap-2 h-40">
                    @foreach($messagesChart as $i => $value)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <span class="text-xs font-semibold text-ink-500">{{ $value }}</span>
                            <div class="w-full bg-clay-500 rounded-t-md" style="height: {{ max(4, ($value / $maxMessages) * 100) }}%"></div>
                            <span class="text-xs text-ink-400">{{ $months[$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Détail par service --}}
        <div class="bg-cream-50 rounded-xl border border-ink-100 overflow-hidden mb-8">
            <div class="p-6 pb-4">
                <h2 class="font-bold text-ink-900">Performance par service</h2>
                <p class="text-xs text-ink-400 mt-0.5">Lequel attire le regard, lequel convertit vraiment en commandes.</p>
            </div>
            @forelse($servicesStats as $row)
                <div class="flex items-center justify-between gap-4 px-6 py-3.5 border-t border-ink-100">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-ink-900 text-sm truncate">{{ $row->title }}</p>
                        @if($row->status !== 'active')
                            <span class="text-[11px] text-ink-400">{{ \App\Models\Service::STATUS_LABELS[$row->status] ?? $row->status }}</span>
                        @endif
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-ink-400">{{ $row->views }} vue(s) · {{ $row->orders }} commande(s)</p>
                        <p class="text-sm font-bold {{ $row->conversion !== null ? 'text-ink-900' : 'text-ink-300' }}">
                            {{ $row->conversion !== null ? $row->conversion . '%' : '—' }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="px-6 py-8 text-sm text-ink-400">Aucun service pour le moment.</p>
            @endforelse
        </div>

        @if($categoryBreakdown !== null)
            {{-- Premium uniquement --}}
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                <div class="flex items-center gap-2 mb-6">
                    <x-app-icon name="sparkles" class="w-4 h-4 text-ochre-600" />
                    <h2 class="font-bold text-ink-900">Répartition par catégorie</h2>
                    <span class="text-xs text-ink-400">(Premium)</span>
                </div>

                @forelse($categoryBreakdown as $row)
                    @php $share = $totalRevenue > 0 ? ($row->revenue / $totalRevenue) * 100 : 0; @endphp
                    <div class="mb-4 last:mb-0">
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="font-semibold text-ink-700">{{ $row->category }}</span>
                            <span class="text-ink-500">{{ $row->orders_count }} commande(s) · {{ number_format($row->revenue, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="w-full bg-ink-100/40 rounded-full h-2">
                            <div class="bg-ochre-500 h-2 rounded-full" style="width: {{ max(2, $share) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-ink-400 text-sm">Pas encore assez de données.</p>
                @endforelse
            </div>
        @endif

        @if($categoryBenchmark !== null && $categoryBenchmark->isNotEmpty())
            {{-- Premium uniquement --}}
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 mt-8">
                <div class="flex items-center gap-2 mb-6">
                    <x-app-icon name="sparkles" class="w-4 h-4 text-ochre-600" />
                    <h2 class="font-bold text-ink-900">Votre conversion vs la moyenne de la catégorie</h2>
                    <span class="text-xs text-ink-400">(Premium)</span>
                </div>

                @foreach($categoryBenchmark as $row)
                    <div class="mb-4 last:mb-0 flex items-center justify-between gap-4">
                        <span class="font-semibold text-ink-700 text-sm">{{ $row->category }}</span>
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-ink-900 font-bold">
                                Vous : {{ $row->my_rate !== null ? $row->my_rate . '%' : '—' }}
                            </span>
                            <span class="text-ink-400">
                                Catégorie : {{ $row->category_rate !== null ? $row->category_rate . '%' : '—' }}
                            </span>
                            @if($row->my_rate !== null && $row->category_rate !== null)
                                @if($row->my_rate > $row->category_rate)
                                    <span class="text-forest-700 font-bold text-xs">↑ au-dessus</span>
                                @elseif($row->my_rate < $row->category_rate)
                                    <span class="text-red-600 font-bold text-xs">↓ en dessous</span>
                                @else
                                    <span class="text-ink-400 font-bold text-xs">= dans la moyenne</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
