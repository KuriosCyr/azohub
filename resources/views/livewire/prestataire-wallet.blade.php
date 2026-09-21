<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="mb-8">
            <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">Portefeuille</h1>
            <p class="text-ink-500">Suivez vos gains et demandez le retrait de votre solde disponible.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-forest-600/10 border border-forest-600/20 text-forest-700 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Solde --}}
        <div class="bg-ink-900 text-cream-50 rounded-xl p-8 mb-8 flex flex-wrap items-center justify-between gap-6">
            <div>
                <p class="text-ink-300 text-sm mb-1">Solde disponible</p>
                <p class="text-4xl font-serif font-medium">{{ number_format(Auth::user()->wallet_balance, 0, ',', ' ') }} <span class="text-xl text-ink-300">FCFA</span></p>
            </div>
            <button wire:click="toggleRequestForm"
                    class="bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition shadow-md">
                {{ $showRequestForm ? 'Annuler' : 'Demander un retrait' }}
            </button>
        </div>

        {{-- Formulaire de retrait --}}
        @if($showRequestForm)
            <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 mb-8 space-y-4">
                <h2 class="font-bold text-ink-900">Nouvelle demande de retrait</h2>

                <div>
                    <label class="block text-sm font-semibold text-ink-700 mb-2">Montant (FCFA)</label>
                    <input type="number" wire:model="amount" placeholder="Ex : 10000"
                           class="w-full px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-ink-400 mt-1">Montant minimum : {{ number_format(\App\Livewire\PrestataireWallet::MIN_WITHDRAWAL, 0, ',', ' ') }} FCFA</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-ink-700 mb-2">Mode de réception</label>
                    <x-payment-methods wire:model="paymentMethod" name="withdrawal_method" :methods="['mtn_momo', 'moov_money', 'celtiis_cash']" cols="grid-cols-3" compact />
                    @error('paymentMethod') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-ink-700 mb-2">Numéro de réception</label>
                    <input type="text" wire:model="phoneNumber" placeholder="+229 XX XX XX XX"
                           class="w-full px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
                    @error('phoneNumber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button wire:click="requestWithdrawal" wire:loading.attr="disabled" wire:target="requestWithdrawal"
                        class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-3 rounded-lg transition disabled:opacity-60">
                    Envoyer la demande
                </button>
            </div>
        @endif

        {{-- Demandes de retrait --}}
        <div class="mb-10">
            <h2 class="text-xl font-bold text-ink-900 mb-4">Mes demandes de retrait</h2>
            <div class="bg-cream-50 rounded-xl border border-ink-100 divide-y divide-ink-100 overflow-hidden">
                @forelse($withdrawals as $withdrawal)
                    <div class="flex items-center justify-between p-5">
                        <div>
                            <p class="font-bold text-ink-900">{{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-ink-400 mt-1">
                                {{ ['mtn_momo' => 'MTN MoMo', 'moov_money' => 'Moov Money', 'celtiis_cash' => 'Celtiis Cash'][$withdrawal->payment_method] ?? $withdrawal->payment_method }}
                                · {{ $withdrawal->phone_number }} · {{ $withdrawal->created_at->format('d/m/Y') }}
                            </p>
                            @if($withdrawal->status === 'rejected' && $withdrawal->admin_note)
                                <p class="text-xs text-red-500 mt-1">{{ $withdrawal->admin_note }}</p>
                            @endif
                        </div>
                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full flex-shrink-0
                            {{ $withdrawal->status === 'paid' ? 'bg-forest-600/10 text-forest-700' : ($withdrawal->status === 'rejected' ? 'bg-red-50 text-red-600' : 'bg-ochre-100 text-ochre-700') }}">
                            {{ $withdrawal->status === 'paid' ? 'Payé' : ($withdrawal->status === 'rejected' ? 'Refusé' : 'En attente') }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-ink-400">Aucune demande de retrait pour l'instant.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">{{ $withdrawals->links() }}</div>
        </div>

        {{-- Historique des gains --}}
        <div>
            <h2 class="text-xl font-bold text-ink-900 mb-4">Historique des gains</h2>
            <div class="bg-cream-50 rounded-xl border border-ink-100 divide-y divide-ink-100 overflow-hidden">
                @forelse($earnings as $order)
                    <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between p-5 hover:bg-ink-100/20 transition">
                        <div class="min-w-0">
                            <p class="font-semibold text-ink-900 truncate">{{ $order->display_title }}</p>
                            <p class="text-xs text-ink-400 mt-1">Commande {{ $order->order_number }} · {{ $order->validated_at?->format('d/m/Y') }}</p>
                        </div>
                        <p class="font-bold text-forest-700 flex-shrink-0">+{{ number_format($order->prestataire_amount, 0, ',', ' ') }} FCFA</p>
                    </a>
                @empty
                    <div class="text-center py-12">
                        <p class="text-ink-400">Aucun gain enregistré pour l'instant.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">{{ $earnings->links() }}</div>
        </div>
    </div>
</div>
