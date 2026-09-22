@props(['review', 'showService' => false])

<div class="bg-cream-50 border border-ink-100 rounded-lg p-6 hover:shadow-md transition">
    <div class="flex items-start gap-4">
        {{-- Avatar --}}
        <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
             alt="{{ $review->reviewer->name }}"
             class="w-16 h-16 rounded-full border border-ink-100 flex-shrink-0">

        <div class="flex-1 min-w-0">
            {{-- En-tête --}}
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-semibold text-ink-900 text-lg">{{ $review->reviewer->name }}</h3>
                    @if($showService && $review->order && $review->order->service)
                        <p class="text-sm text-ink-500">{{ $review->order->service->title }}</p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="flex items-center gap-1 mb-1">
                        <x-star-rating :rating="$review->rating" class="w-5 h-5" />
                        <span class="font-semibold text-ink-900">{{ $review->rating }}</span>
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
                <div class="bg-ink-100/20 p-4 rounded-lg mb-3">
                    <p class="text-ink-700 text-sm leading-relaxed">
                        {{ Str::limit($review->comment, 200) }}
                    </p>
                </div>
            @endif

            <livewire:review-response :review="$review" :key="'review-response-'.$review->id" />

            {{-- Action --}}
            <a href="{{ route('reviews.show', $review) }}"
               class="text-terracotta-600 hover:text-terracotta-700 font-semibold text-sm inline-flex items-center gap-1 transition">
                Voir l'avis complet &rarr;
            </a>
        </div>
    </div>
</div>
