<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl">
        <nav class="mb-6 text-sm">
            <ol class="flex items-center gap-2 text-ink-500">
                <li><a href="{{ route('home') }}" class="hover:text-terracotta-600">Accueil</a></li>
                <li>→</li>
                <li><a href="{{ route('service-requests.show', $serviceRequest) }}" class="hover:text-terracotta-600">{{ Str::limit($serviceRequest->title, 40) }}</a></li>
                <li>→</li>
                <li class="text-ink-900 font-semibold">Accepter la proposition</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h1 class="text-2xl font-serif font-medium text-ink-900 mb-6">Confirmer la commande</h1>

                    {{-- Proposition résumée --}}
                    <div class="flex gap-4 p-4 bg-terracotta-50 rounded-lg mb-8">
                        <img src="{{ $proposal->prestataire->avatar ? Storage::url($proposal->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($proposal->prestataire->name) }}"
                             alt="{{ $proposal->prestataire->name }}" class="w-16 h-16 rounded-xl border border-ink-200 flex-shrink-0 object-cover">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-ink-900">{{ $proposal->prestataire->name }}</p>
                            <p class="text-sm text-ink-500 mt-1">Pour la demande « {{ Str::limit($serviceRequest->title, 50) }} »</p>
                            <p class="text-sm text-terracotta-700 font-semibold mt-1">Livraison en {{ $proposal->delivery_time }} jour(s)</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm font-bold text-ink-700 mb-2">Message du prestataire</p>
                        <p class="text-ink-600 bg-ink-100/20 rounded-lg p-4 whitespace-pre-line">{{ $proposal->message }}</p>
                    </div>

                    {{-- Mode de paiement --}}
                    <div class="mb-2">
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
                        $clientFee = $proposal->proposed_price * \App\Models\Order::CLIENT_FEE_RATE;
                    @endphp
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-500">Prix proposé</span>
                            <span class="font-semibold">{{ number_format($proposal->proposed_price, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-500">Frais de service ({{ number_format(\App\Models\Order::CLIENT_FEE_RATE * 100, 0, ',', ' ') }}%)</span>
                            <span class="font-semibold">{{ number_format($clientFee, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="border-t border-ink-100 pt-3 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-ink-900">{{ number_format($proposal->proposed_price + $clientFee, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>

                    <button
                        wire:click="confirm"
                        wire:loading.attr="disabled"
                        wire:target="confirm"
                        class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-4 rounded-lg transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed mb-4"
                    >
                        <span wire:loading.remove wire:target="confirm">Confirmer et payer</span>
                        <span wire:loading wire:target="confirm">Traitement...</span>
                    </button>

                    <a href="{{ route('service-requests.show', $serviceRequest) }}"
                       class="block text-center text-sm text-ink-400 hover:text-ink-700 font-semibold">
                        Annuler
                    </a>

                    <div class="mt-6 pt-6 border-t border-ink-100 space-y-3">
                        @foreach([
                            'Paiement sécurisé par escrow',
                            'Remboursement si non livré',
                            'Les autres propositions seront automatiquement refusées',
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
