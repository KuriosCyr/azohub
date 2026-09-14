@props(['review', 'showService' => false])

<div class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-xl transition">
    <div class="flex items-start gap-4">
        {{-- Avatar --}}
        <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}" 
             alt="{{ $review->reviewer->name }}"
             class="w-16 h-16 rounded-full border-4 border-blue-100 flex-shrink-0">

        <div class="flex-1 min-w-0">
            {{-- En-tête --}}
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">{{ $review->reviewer->name }}</h3>
                    @if($showService && $review->order && $review->order->service)
                        <p class="text-sm text-gray-600">{{ $review->order->service->title }}</p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <div class="flex items-center gap-1 mb-1">
                        <div class="flex text-lg">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                            @endfor
                        </div>
                        <span class="font-bold text-gray-900">{{ $review->rating }}</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                </div>
            </div>

            {{-- Notes détaillées (compactes) --}}
            <div class="flex gap-4 mb-4 text-sm">
                <div class="flex items-center gap-1">
                    <span class="text-gray-600">Qualité:</span>
                    <div class="flex">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-xs {{ $i <= $review->quality_rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                        @endfor
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-gray-600">Communication:</span>
                    <div class="flex">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-xs {{ $i <= $review->communication_rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                        @endfor
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-gray-600">Délais:</span>
                    <div class="flex">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-xs {{ $i <= $review->timeliness_rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Commentaire --}}
            @if($review->comment)
                <div class="bg-gray-50 p-4 rounded-xl mb-3">
                    <p class="text-gray-700 text-sm leading-relaxed">
                        {{ Str::limit($review->comment, 200) }}
                    </p>
                </div>
            @endif

            {{-- Action --}}
            <a href="{{ route('reviews.show', $review) }}" 
               class="text-blue-900 hover:text-blue-700 font-bold text-sm inline-flex items-center gap-1 transition">
                Voir l'avis complet
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>