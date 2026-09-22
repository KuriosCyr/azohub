<div class="min-h-screen bg-cream py-10">
    <div class="container mx-auto px-4 max-w-3xl">
        <a href="{{ url()->previous() }}" class="text-ink-500 hover:text-terracotta-600 font-bold text-sm mb-6 inline-block transition">
            ← Retour
        </a>

        <div class="bg-cream-50 rounded-xl border border-ink-100 p-8 mb-6">
            <p class="text-xs font-semibold text-ink-400 uppercase tracking-wide mb-4">
                Profil visible uniquement par les prestataires ayant échangé avec ce client
            </p>
            <div class="flex items-center gap-5">
                <img src="{{ $client->avatar ? Storage::url($client->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($client->name) }}"
                     alt="{{ $client->name }}"
                     class="w-20 h-20 rounded-full border-4 border-ink-100 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-serif font-medium text-ink-900">{{ $client->name }}</h1>
                    <p class="text-ink-500 text-sm mt-0.5">{{ $client->city ?? 'Bénin' }} · Membre depuis {{ $client->created_at->translatedFormat('M Y') }}</p>
                    <div class="flex items-center gap-4 mt-3">
                        <div class="flex items-center gap-1.5">
                            <x-star-rating :rating="$client->rating" class="w-4 h-4" />
                            <span class="font-bold text-ink-900 text-sm">{{ number_format($client->rating, 1) }}</span>
                            <span class="text-ink-400 text-sm">({{ $client->total_reviews }} avis)</span>
                        </div>
                        <span class="text-ink-300">·</span>
                        <span class="text-sm text-ink-500">{{ $completedOrdersWithMe }} commande(s) terminée(s) avec vous</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-cream-50 rounded-xl border border-ink-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-ink-100">
                <h2 class="text-base font-bold text-ink-900">Avis laissés par des prestataires</h2>
            </div>

            <div class="divide-y divide-ink-100">
                @forelse($reviews as $review)
                    <div class="px-6 py-5">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                                     alt="{{ $review->reviewer->name }}"
                                     class="w-9 h-9 rounded-full flex-shrink-0">
                                <div>
                                    <p class="font-bold text-ink-900 text-sm">{{ $review->reviewer->name }}</p>
                                    @if($review->order->service)
                                        <p class="text-xs text-ink-400">{{ $review->order->service->title }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <x-star-rating :rating="$review->rating" class="w-4 h-4" />
                                <span class="font-bold text-ink-900 text-sm">{{ $review->rating }}</span>
                            </div>
                        </div>
                        @if($review->comment)
                            <p class="text-ink-700 text-sm bg-cream rounded-xl px-4 py-3 mt-2 leading-relaxed">{{ $review->comment }}</p>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="w-14 h-14 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-3">
                            <x-app-icon name="chat" class="w-7 h-7 text-ink-300" />
                        </div>
                        <p class="text-ink-500 text-sm font-medium">Aucun avis prestataire pour le moment</p>
                    </div>
                @endforelse
            </div>

            @if($reviews->hasPages())
                <div class="px-6 py-4 border-t border-ink-100">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
