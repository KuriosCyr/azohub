<div class="min-h-screen bg-cream py-8">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <a href="{{ route('prestataire.dashboard') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold mb-4 inline-block text-sm">
                ← Tableau de bord
            </a>
            <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="box" class="w-8 h-8 inline-block" /> Mes commandes</h1>
            <p class="text-ink-500">{{ $counts['all'] }} commande(s) au total</p>
        </div>

        {{-- Filtres --}}
        <div class="bg-cream-50 rounded-xl p-4 md:p-6 border border-ink-100 mb-6">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                @foreach([
                    'all' => 'Toutes',
                    'active' => 'Actives',
                    'delivered' => 'Livrées',
                    'completed' => 'Terminées',
                    'cancelled' => 'Annulées',
                    'disputed' => 'Litiges',
                ] as $key => $label)
                    <button wire:click="$set('status', '{{ $key }}')"
                            class="px-5 py-2.5 rounded-full font-bold text-sm transition {{ $status === $key ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        {{ $label }} ({{ $counts[$key] }})
                    </button>
                @endforeach
            </div>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Rechercher par n° de commande ou nom du client…"
                   class="w-full px-4 py-2.5 border-2 border-ink-200 rounded-xl text-sm focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
        </div>

        <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-ink-100">
                @forelse($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="block px-6 py-5 hover:bg-ink-100/30 transition">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2.5 mb-1">
                                    <span class="font-bold text-ink-400 text-xs">#{{ $order->order_number }}</span>
                                    @php
                                        $statusMap = [
                                            'pending_payment' => ['bg-ink-100 text-ink-500', 'En attente de paiement'],
                                            'paid'        => ['bg-terracotta-50 text-terracotta-700', 'Nouvelle'],
                                            'in_progress' => ['bg-ochre-500/15 text-ink-900', 'En cours'],
                                            'delivered'   => ['bg-clay-500/15 text-ink-900', 'Livrée'],
                                            'completed'   => ['bg-forest-600/10 text-forest-700', 'Terminée'],
                                            'cancelled'   => ['bg-red-100 text-red-800', 'Annulée'],
                                            'disputed'    => ['bg-red-100 text-red-800', 'En litige'],
                                        ];
                                        [$badgeClass, $badgeLabel] = $statusMap[$order->status] ?? ['bg-ink-100 text-ink-700', ucfirst($order->status)];
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </div>
                                <p class="font-bold text-ink-900 text-sm truncate">{{ $order->display_title }}</p>
                                <p class="text-xs text-ink-400 mt-0.5">Client : {{ $order->client->name }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xl font-bold text-ink-900">{{ number_format($order->prestataire_amount, 0, ',', ' ') }} F</p>
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
                        <p class="text-ink-500 font-medium">Aucune commande ne correspond à ce filtre</p>
                    </div>
                @endforelse
            </div>

            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-ink-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
