<div class="min-h-screen bg-cream py-8">
    <div class="container mx-auto px-4 max-w-3xl">
        <div class="mb-8">
            <a href="{{ route('prestataire.dashboard') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold mb-4 inline-block text-sm">
                ← Tableau de bord
            </a>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h1 class="text-4xl font-serif font-medium text-ink-900"><x-app-icon name="star" class="w-8 h-8 inline-block" /> Mes avis</h1>
                <div class="flex items-center gap-1.5 bg-ochre-500/15 px-4 py-2 rounded-full">
                    <svg class="w-4 h-4 text-ochre-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span class="font-bold text-ink-900">{{ number_format($rating, 1) }}/5</span>
                    <span class="text-ink-500 text-sm">({{ $totalReviews }} avis)</span>
                </div>
            </div>
        </div>

        <div class="bg-cream-50 rounded-lg border border-ink-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-ink-100">
                @forelse($reviews as $review)
                    <div class="px-6 py-5">
                        <div class="flex gap-4">
                            <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                                 alt="{{ $review->reviewer->name }}"
                                 class="w-11 h-11 rounded-full flex-shrink-0">
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-bold text-ink-900 text-sm">{{ $review->reviewer->name }}</p>
                                        <p class="text-xs text-ink-400">{{ $review->order->service->title ?? '—' }}</p>
                                    </div>
                                    <span class="text-xs text-ink-400">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>

                                <div class="flex items-center gap-4 mb-3 flex-wrap">
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

                                <livewire:review-response :review="$review" :key="'review-response-'.$review->id" />
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

            @if($reviews->hasPages())
                <div class="px-6 py-4 border-t border-ink-100">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
