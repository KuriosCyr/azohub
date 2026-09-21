<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl">
        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm">
            <ol class="flex items-center gap-2 text-ink-500">
                <li><a href="{{ route('home') }}" class="hover:text-terracotta-600">Accueil</a></li>
                <li>→</li>
                <li><a href="{{ route('services.show', $service) }}" class="hover:text-terracotta-600">{{ Str::limit($service->title, 40) }}</a></li>
                <li>→</li>
                <li class="text-ink-900 font-semibold">Commander</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Formulaire --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h1 class="text-2xl font-serif font-medium text-ink-900 mb-6">Votre commande</h1>

                    {{-- Service résumé --}}
                    <div class="flex gap-4 p-4 bg-terracotta-50 rounded-lg mb-8">
                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0">
                            <x-service-cover :service="$service" class="w-full h-full object-cover" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-ink-900 line-clamp-2">{{ $service->title }}</p>
                            <p class="text-sm text-ink-500 mt-1">Par {{ $service->prestataire->name }}</p>
                            <p class="text-sm text-terracotta-700 font-semibold mt-1">Livraison en {{ $service->delivery_time }} jour(s)</p>
                        </div>
                    </div>

                    {{-- Vos besoins --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Décrivez vos besoins <span class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-ink-400 mb-3">
                            Expliquez précisément ce que vous attendez du prestataire pour cette commande.
                        </p>
                        <textarea
                            wire:model="requirements"
                            rows="6"
                            placeholder="Exemple : Je souhaite un logo moderne pour mon entreprise de livraison. Couleurs : bleu et orange. Style : minimaliste. Je fournirai les textes..."
                            class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition resize-none"
                        ></textarea>
                        @error('requirements')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-ink-300 mt-1">{{ strlen($requirements) }}/2000 caractères (min. 20)</p>
                    </div>

                    {{-- Mode de paiement --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-ink-700 mb-3">
                            Mode de paiement <span class="text-red-500">*</span>
                        </label>
                        <x-payment-methods wire:model="paymentMethod" />
                        @error('paymentMethod')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Récapitulatif --}}
            <div class="lg:col-span-1">
                <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 sticky top-24">
                    <h2 class="text-lg font-bold text-ink-900 mb-6">Récapitulatif</h2>

                    @php
                        $clientFee = $service->price * \App\Models\Order::CLIENT_FEE_RATE;
                    @endphp
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-500">Prix du service</span>
                            <span class="font-semibold">{{ number_format($service->price, 0) }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-500">Frais de service ({{ number_format(\App\Models\Order::CLIENT_FEE_RATE * 100, 0) }}%)</span>
                            <span class="font-semibold">{{ number_format($clientFee, 0) }} FCFA</span>
                        </div>
                        <div class="border-t border-ink-100 pt-3 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-ink-900">{{ number_format($service->price + $clientFee, 0) }} FCFA</span>
                        </div>
                    </div>

                    <button
                        wire:click="placeOrder"
                        wire:loading.attr="disabled"
                        class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-4 rounded-lg transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed mb-4"
                    >
                        <span wire:loading.remove wire:target="placeOrder">Confirmer et payer</span>
                        <span wire:loading wire:target="placeOrder">Traitement...</span>
                    </button>

                    <a href="{{ route('services.show', $service) }}"
                       class="block text-center text-sm text-ink-400 hover:text-ink-700 font-semibold">
                        Annuler
                    </a>

                    {{-- Garanties --}}
                    <div class="mt-6 pt-6 border-t border-ink-100 space-y-3">
                        @foreach([
                            'Paiement sécurisé par escrow',
                            'Remboursement si non livré',
                            'Support disponible 24/7',
                        ] as $guarantee)
                            <div class="flex items-center gap-3 text-sm text-ink-500">
                                <svg class="w-4 h-4 text-forest-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $guarantee }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
