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

        {{-- Graphiques --}}
        <div class="grid md:grid-cols-2 gap-6 mb-8">
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
    </div>
</div>
