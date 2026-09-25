<div class="min-h-screen bg-cream py-8">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <a href="{{ route('client.dashboard') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold mb-4 inline-block text-sm">
                ← Tableau de bord
            </a>
            <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="cart" class="w-8 h-8 inline-block" /> Mes commandes</h1>
            <p class="text-ink-500">{{ $counts['all'] }} commande(s) au total</p>
        </div>

        {{-- Filtres --}}
        <div class="bg-cream-50 rounded-xl p-4 md:p-6 border border-ink-100 mb-6">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                @foreach([
                    'all' => 'Toutes',
                    'active' => 'En cours',
                    'awaiting' => 'À valider',
                    'completed' => 'Terminées',
                    'cancelled' => 'Annulées',
                ] as $key => $label)
                    <button wire:click="$set('status', '{{ $key }}')"
                            class="px-5 py-2.5 rounded-full font-bold text-sm transition {{ $status === $key ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/30 text-ink-700 hover:bg-ink-100/50' }}">
                        {{ $label }} ({{ $counts[$key] }})
                    </button>
                @endforeach
            </div>
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Rechercher par n° de commande ou nom du prestataire…"
                   class="w-full px-4 py-2.5 border-2 border-ink-200 rounded-xl text-sm focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
        </div>

        <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-ink-100">
                @forelse($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="block px-6 py-5 hover:bg-ink-100/30 transition">
                        <div class="flex items-center gap-4">
                            <img src="{{ $order->prestataire->avatar ? Storage::url($order->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($order->prestataire->name) }}"
                                 alt="{{ $order->prestataire->name }}"
                                 class="w-12 h-12 rounded-xl border border-ink-100 flex-shrink-0">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <h3 class="font-bold text-ink-900 text-sm truncate">{{ $order->display_title }}</h3>
                                    <span class="font-bold text-ink-900 text-sm whitespace-nowrap">{{ number_format($order->total_charged, 0, ',', ' ') }} F</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-ink-400 flex-wrap">
                                    <span class="font-bold text-ink-400">#{{ $order->order_number }}</span>
                                    <span>·</span>
                                    <span class="truncate">{{ $order->prestataire->name }}</span>
                                    <span>·</span>
                                    <span class="whitespace-nowrap">{{ $order->created_at->format('d/m/Y') }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                                </div>
                            </div>

                            <span class="text-terracotta-600 font-semibold text-xs whitespace-nowrap flex-shrink-0">Voir →</span>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="w-12 h-12 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-3">
                            <x-app-icon name="cart" class="w-6 h-6 text-ink-300" />
                        </div>
                        <p class="text-ink-500 font-medium text-sm">Aucune commande trouvée</p>
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
