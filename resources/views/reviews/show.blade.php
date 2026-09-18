<x-app-layout>
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ url()->previous() }}"
                   class="text-ink-900 hover:text-terracotta-700 font-bold mb-4 inline-block">
                    ← Retour
                </a>
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">
                    Avis détaillé
                </h1>
            </div>

            {{-- Avis principal --}}
            <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                {{-- En-tête avis --}}
                <div class="flex items-start gap-6 mb-8 pb-8 border-b">
                    <img src="{{ $review->reviewer->avatar ? Storage::url($review->reviewer->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->reviewer->name) }}"
                         alt="{{ $review->reviewer->name }}"
                         class="w-20 h-20 rounded-full border-4 border-ink-100">

                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h2 class="text-2xl font-bold text-ink-900">{{ $review->reviewer->name }}</h2>
                                <p class="text-ink-500">{{ $review->reviewer->city ?? 'Bénin' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-star-rating :rating="$review->rating" class="w-6 h-6" />
                                    <span class="text-2xl font-bold text-ink-900">{{ $review->rating }}/5</span>
                                </div>
                                <p class="text-sm text-ink-400">{{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        {{-- Notes détaillées --}}
                        <div class="grid grid-cols-3 gap-4 bg-ink-100/30 p-4 rounded-lg">
                            <div class="text-center">
                                <p class="text-xs text-ink-400 mb-1">Qualité</p>
                                <div class="flex justify-center">
                                    <x-star-rating :rating="$review->quality_rating" class="w-4 h-4" />
                                </div>
                                <p class="font-bold text-ink-900 mt-1">{{ $review->quality_rating }}/5</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-ink-400 mb-1">Communication</p>
                                <div class="flex justify-center">
                                    <x-star-rating :rating="$review->communication_rating" class="w-4 h-4" />
                                </div>
                                <p class="font-bold text-ink-900 mt-1">{{ $review->communication_rating }}/5</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-ink-400 mb-1">Délais</p>
                                <div class="flex justify-center">
                                    <x-star-rating :rating="$review->timeliness_rating" class="w-4 h-4" />
                                </div>
                                <p class="font-bold text-ink-900 mt-1">{{ $review->timeliness_rating }}/5</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Commentaire --}}
                @if($review->comment)
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-ink-900 mb-4">Commentaire</h3>
                        <div class="bg-ink-100/30 p-6 rounded-lg">
                            <p class="text-ink-700 leading-relaxed whitespace-pre-wrap">{{ $review->comment }}</p>
                        </div>
                    </div>
                @endif

                {{-- Info commande --}}
                <div class="bg-terracotta-50 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-ink-900 mb-4">Informations sur la commande</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-ink-500 mb-1">Service</p>
                            <p class="font-bold text-ink-900">{{ $review->order->service->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-ink-500 mb-1">{{ $review->review_type === 'client_to_prestataire' ? 'Prestataire' : 'Client' }}</p>
                            <p class="font-bold text-ink-900">{{ $review->reviewee->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-ink-500 mb-1">Commande</p>
                            <a href="{{ route('orders.show', $review->order) }}" class="font-bold text-ink-900 hover:text-terracotta-700">
                                #{{ $review->order->order_number }}
                            </a>
                        </div>
                        <div>
                            <p class="text-sm text-ink-500 mb-1">Date de livraison</p>
                            <p class="font-bold text-ink-900">{{ $review->order->validated_at ? $review->order->validated_at->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-8 flex gap-4">
                <a href="{{ route('orders.show', $review->order) }}"
                   class="flex-1 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg text-center transition">
                    Voir la commande
                </a>
                @if($review->review_type === 'client_to_prestataire')
                    <a href="{{ route('prestataire.profile', $review->reviewee->slug) }}"
                       class="flex-1 bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-8 py-4 rounded-lg text-center transition">
                        Voir le profil du prestataire
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>