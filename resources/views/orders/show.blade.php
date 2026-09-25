<x-app-layout>
    @php
        $paidPayment = $order->payments->firstWhere('status', 'success');
    @endphp
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4 max-w-6xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ $userRole === 'prestataire' ? route('prestataire.dashboard') : route('client.dashboard') }}"
                    class="text-ink-900 hover:text-terracotta-700 font-bold mb-4 inline-block">
                    ← Retour au dashboard
                </a>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">
                            Commande #{{ $order->order_number }}
                        </h1>
                        <p class="text-ink-500">Créée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                    </div>

                    {{-- Status Badge --}}
                    <div>
                        <span class="px-6 py-3 rounded-full text-lg font-bold {{ $order->status_badge_class }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Compte à rebours de livraison --}}
            @if($order->expected_delivery_at && !in_array($order->status, ['delivered', 'completed', 'cancelled', 'refused', 'refunded'], true))
                <div x-data="{
                        target: {{ $order->expected_delivery_at->timestamp }} * 1000,
                        remaining: 0,
                        tick() {
                            this.remaining = this.target - Date.now();
                            setTimeout(() => this.tick(), 1000);
                        },
                        get overdue() { return this.remaining <= 0; },
                        get d() { return Math.floor(Math.abs(this.remaining) / 86400000); },
                        get h() { return Math.floor((Math.abs(this.remaining) % 86400000) / 3600000); },
                        get m() { return Math.floor((Math.abs(this.remaining) % 3600000) / 60000); },
                     }"
                     x-init="tick()"
                     class="mb-8 rounded-xl p-6 border-2 flex flex-wrap items-center justify-between gap-4"
                     :class="overdue ? 'bg-red-50 border-red-200' : 'bg-clay-500/10 border-clay-500/30'">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide mb-1" :class="overdue ? 'text-red-700' : 'text-ink-500'">
                            <span x-show="!overdue">Livraison attendue dans</span>
                            <span x-show="overdue" style="display:none;">Livraison en retard de</span>
                        </p>
                        <p class="text-2xl font-serif font-medium" :class="overdue ? 'text-red-700' : 'text-ink-900'">
                            <span x-text="d"></span>&nbsp;j
                            <span x-text="h"></span>&nbsp;h
                            <span x-text="m"></span>&nbsp;min
                        </p>
                    </div>
                    <p class="text-sm text-ink-500">
                        Livraison prévue le {{ $order->expected_delivery_at->translatedFormat('d M Y à H\hi') }}
                    </p>
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Colonne principale --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Informations service --}}
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Service commandé</h2>

                        <div class="flex gap-6">
                            <div class="w-32 h-32 rounded-lg overflow-hidden flex-shrink-0">
                                <x-service-cover :service="$order->service" class="w-full h-full object-cover" />
                            </div>

                            <div class="flex-1">
                                @if($order->service)
                                    <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full mb-2 inline-block">
                                        {{ $order->service->category->name }}
                                    </span>
                                    <h3 class="text-xl font-bold text-ink-900 mb-2">{{ $order->service->title }}</h3>
                                    <p class="text-ink-500 mb-4">{{ Str::limit($order->service->description, 150) }}</p>
                                @elseif($order->serviceRequest)
                                    <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full mb-2 inline-block">
                                        {{ $order->serviceRequest->category->name }}
                                    </span>
                                    <h3 class="text-xl font-bold text-ink-900 mb-2">{{ $order->serviceRequest->title }}</h3>
                                    <p class="text-ink-500 mb-4">{{ Str::limit($order->requirements, 150) }}</p>
                                @elseif($order->customOffer)
                                    <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full mb-2 inline-block">
                                        Offre personnalisée
                                    </span>
                                    <h3 class="text-xl font-bold text-ink-900 mb-2">{{ $order->customOffer->title }}</h3>
                                    <p class="text-ink-500 mb-4">{{ Str::limit($order->customOffer->description, 150) }}</p>
                                @else
                                    <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full mb-2 inline-block">
                                        Commande
                                    </span>
                                    <h3 class="text-xl font-bold text-ink-900 mb-2">Commande {{ $order->order_number }}</h3>
                                    @if($order->requirements)
                                        <p class="text-ink-500 mb-4">{{ Str::limit($order->requirements, 150) }}</p>
                                    @endif
                                @endif

                                <div class="flex items-center gap-4">
                                    <div>
                                        <p class="text-sm text-ink-400">Prix</p>
                                        <p class="text-2xl font-bold text-ink-900">{{ number_format($order->amount, 0, ',', ' ') }} FCFA</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-ink-400">Délai</p>
                                        <p class="font-bold text-ink-900">{{ $order->delivery_time }} jours</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline --}}
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Suivi de la commande</h2>

                        <div class="space-y-4">
                            {{-- Commandé --}}
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                                        <x-app-icon name="check" class="w-5 h-5" />
                                    </div>
                                    @if(!($order->status === 'cancelled' && !$paidPayment))
                                    <div class="w-1 h-full bg-green-500"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-8">
                                    <p class="font-bold text-ink-900">Commande créée</p>
                                    <p class="text-sm text-ink-400">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>

                            {{-- Payé --}}
                            @if($paidPayment)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                                        <x-app-icon name="check" class="w-5 h-5" />
                                    </div>
                                    @if($order->status !== 'cancelled')
                                    <div class="w-1 h-full {{ in_array($order->status, ['in_progress', 'delivered', 'completed']) ? 'bg-green-500' : 'bg-ink-200' }}"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-8">
                                    <p class="font-bold text-ink-900">Paiement reçu</p>
                                    <p class="text-sm text-ink-400">{{ $paidPayment->paid_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            {{-- En cours --}}
                            @if($order->accepted_at)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                                        <x-app-icon name="check" class="w-5 h-5" />
                                    </div>
                                    @if(in_array($order->status, ['delivered', 'completed']))
                                    <div class="w-1 h-full bg-green-500"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-8">
                                    <p class="font-bold text-ink-900">Travail commencé</p>
                                    <p class="text-sm text-ink-400">{{ $order->accepted_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            @elseif($order->status === 'in_progress')
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-ochre-500 flex items-center justify-center text-white font-bold animate-pulse">
                                        <x-app-icon name="cog" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="flex-1 pb-8">
                                    <p class="font-bold text-ink-900">En cours de réalisation...</p>
                                </div>
                            </div>
                            @endif

                            {{-- Livré --}}
                            @if($order->delivered_at)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full {{ $order->status === 'completed' ? 'bg-green-500' : 'bg-purple-500' }} flex items-center justify-center text-white font-bold">
                                        <x-app-icon name="check" class="w-5 h-5" />
                                    </div>
                                    @if($order->status === 'completed')
                                    <div class="w-1 h-full bg-green-500"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-8">
                                    <p class="font-bold text-ink-900">Travail livré</p>
                                    <p class="text-sm text-ink-400">{{ $order->delivered_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            {{-- Validé --}}
                            @if($order->validated_at)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                                        <x-app-icon name="check" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-forest-700">Commande terminée !</p>
                                    <p class="text-sm text-ink-400">{{ $order->validated_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            @endif

                            {{-- Annulé --}}
                            @if($order->cancelled_at)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold">
                                        <x-app-icon name="x-mark" class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-red-600">Commande annulée</p>
                                    <p class="text-sm text-ink-400">{{ $order->cancelled_at->format('d/m/Y à H:i') }}</p>
                                    @if($order->cancellation_reason)
                                    <p class="text-sm text-ink-500 mt-1">Raison : {{ $order->cancellation_reason }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Historique détaillé des statuts --}}
                    @if($order->statusHistory->count() > 1)
                    <details class="bg-cream-50 rounded-xl border border-ink-100">
                        <summary class="cursor-pointer p-5 font-bold text-ink-900 text-sm select-none">
                            Historique détaillé ({{ $order->statusHistory->count() }} changements)
                        </summary>
                        <div class="px-5 pb-5 space-y-2">
                            @foreach($order->statusHistory as $entry)
                                <div class="flex items-center justify-between text-sm border-t border-ink-100 pt-2">
                                    <span class="text-ink-700 font-semibold">{{ \App\Models\Order::statusLabel($entry->status) }}</span>
                                    <span class="text-ink-400">
                                        {{ $entry->created_at->format('d/m/Y H:i') }}
                                        @if($entry->updatedBy)
                                            · {{ $entry->updatedBy->name }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    {{-- Livrables --}}
                    @if($order->deliverables)
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Fichiers livrés</h2>

                        @if($order->delivery_note)
                        <div class="bg-terracotta-50 border-l-4 border-terracotta-600 p-4 mb-6 rounded">
                            <p class="text-sm font-bold text-ink-900 mb-1">Note du prestataire :</p>
                            <p class="text-ink-700">{{ $order->delivery_note }}</p>
                        </div>
                        @endif

                        <div class="space-y-3">
                            @foreach($order->deliverables as $index => $file)
                            <a href="{{ route('orders.deliverable.download', [$order, $index]) }}"
                                class="flex items-center justify-between p-4 bg-ink-100/30 hover:bg-ink-100/50 rounded-lg transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-terracotta-50 rounded-xl flex items-center justify-center">
                                        <x-app-icon name="paperclip" class="w-6 h-6 text-terracotta-600" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-ink-900 group-hover:text-terracotta-700">{{ $file['name'] }}</p>
                                        <p class="text-sm text-ink-400">{{ number_format($file['size'] / 1024, 2) }} KB</p>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-ink-300 group-hover:text-terracotta-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Actions selon le rôle et le statut --}}
                    @if($userRole === 'prestataire')
                    {{-- Actions PRESTATAIRE --}}
                    @if($order->status === 'paid')
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Actions requises</h2>

                        <div class="flex gap-4">
                            <form action="{{ route('orders.accept', $order) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-forest-600 hover:bg-forest-700 text-white font-bold px-6 py-4 rounded-lg transition">
                                    <x-app-icon name="check" class="w-5 h-5 inline-block" /> Accepter la commande
                                </button>
                            </form>

                            <button onclick="document.getElementById('refuse-modal').classList.remove('hidden')"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold px-6 py-4 rounded-lg transition">
<x-app-icon name="x-mark" class="w-5 h-5 inline-block" /> Refuser
                            </button>
                        </div>
                    </div>
                    @elseif($order->status === 'in_progress')
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Livrer le travail</h2>

                        <form action="{{ route('orders.deliver', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <div>
                                <label class="block text-sm font-bold text-ink-700 mb-2">
                                    Note de livraison (optionnel)
                                </label>
                                <textarea name="delivery_notes" rows="4"
                                    class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
                                    placeholder="Décrivez ce que vous avez livré, des instructions, etc."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-ink-700 mb-2">
                                    Fichiers à livrer (optionnel)
                                </label>
                                <input type="file" name="deliverables[]" multiple
                                    class="w-full text-sm text-ink-500 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-terracotta-50 file:text-terracotta-700 hover:file:bg-terracotta-50/70">
                                <p class="text-xs text-ink-400 mt-2">Max 10 MB par fichier</p>
                            </div>

                            <button type="submit"
                                class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition">
<x-app-icon name="upload" class="w-5 h-5 inline-block" /> Marquer comme livré
                            </button>
                        </form>
                    </div>
                    @endif
                    @else
                    {{-- Actions CLIENT --}}
                    @if($order->status === 'pending_payment')
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Finaliser le paiement</h2>

                        <div class="bg-ochre-500/15 border-l-4 border-ochre-500 p-4 mb-6 rounded">
                            <p class="text-sm text-ink-900">
                                Cette commande est en attente de paiement. Le prestataire ne sera notifié qu'une fois le paiement confirmé.
                            </p>
                            @if(session('success') && str_contains(session('success'), 'en cours de confirmation'))
                                <p class="text-xs text-ink-500 mt-2">
                                    Cette page se mettra à jour automatiquement dès que le paiement sera confirmé — inutile de recharger.
                                </p>
                            @endif
                        </div>

                        <form action="{{ route('orders.pay', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-bold text-ink-700 mb-2">
                                    Moyen de paiement
                                </label>
                                <x-payment-methods name="payment_method" selected="mtn_momo" />
                            </div>
                            <button type="submit"
                                class="w-full bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-4 rounded-lg transition">
<x-app-icon name="card" class="w-5 h-5 inline-block" /> Payer {{ number_format($order->total_charged, 0, ',', ' ') }} FCFA
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($order->status === 'delivered')
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Valider la livraison</h2>

                        <div class="bg-ochre-500/15 border-l-4 border-ochre-500 p-4 mb-6 rounded">
                            <p class="text-sm text-ink-900">
                                Vérifiez que le travail correspond à vos attentes avant de valider.
                                Une fois validé, le paiement sera libéré au prestataire.
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <form action="{{ route('orders.validate', $order) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-forest-600 hover:bg-forest-700 text-white font-bold px-6 py-4 rounded-lg transition">
<x-app-icon name="check" class="w-5 h-5 inline-block" /> Valider et finaliser
                                </button>
                            </form>

                            <button onclick="document.getElementById('revision-modal').classList.remove('hidden')"
                                class="flex-1 bg-ochre-600 hover:bg-ochre-500 text-white font-bold px-6 py-4 rounded-lg transition">
                                Demander une révision
                            </button>
                        </div>
                    </div>
                    @endif

                    @if(in_array($order->status, ['pending_payment', 'paid']))
                    <details class="bg-cream-50 rounded-xl border border-ink-100 p-6 mt-6">
                        <summary class="cursor-pointer text-sm font-bold text-red-600 hover:text-red-700">Annuler cette commande</summary>
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <label class="block text-sm font-bold text-ink-700">Motif de l'annulation</label>
                            <textarea name="cancellation_reason" rows="3" required maxlength="500"
                                class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition"
                                placeholder="Expliquez brièvement pourquoi vous annulez"></textarea>
                            @if($order->status === 'paid')
                                <p class="text-xs text-ink-400">Le remboursement est traité manuellement sous 3 à 7 jours ouvrés.</p>
                            @endif
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold px-6 py-3 rounded-lg transition">
                                Confirmer l'annulation
                            </button>
                        </form>
                    </details>
                    @endif
                    @endif {{-- fin des actions propres au rôle --}}

                    @if($order->canOpenDispute())
                    <div class="bg-cream-50 rounded-xl border border-ink-100 p-6 mt-6">
                        <p class="text-sm text-ink-500 mb-3">
                            @if($userRole === 'prestataire')Un désaccord avec le client que vous ne parvenez pas à régler ? Vous pouvez signaler un litige — notre équipe examinera la situation.@elseUn problème que la révision ne résout pas ? Vous pouvez signaler un litige — notre équipe examinera la situation.@endif
                        </p>
                        <button onclick="document.getElementById('dispute-modal').classList.remove('hidden')"
                            class="text-sm font-bold text-red-600 hover:text-red-700 transition">
                            <x-app-icon name="exclamation-triangle" class="w-4 h-4 inline-block align-text-bottom" /> Signaler un litige
                        </button>
                    </div>
                    @endif

                    @if($order->status === 'disputed' && $order->dispute)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mt-6">
                        <h3 class="text-lg font-bold text-red-900 mb-2">
                            <x-app-icon name="exclamation-triangle" class="w-5 h-5 inline-block align-text-bottom" /> Litige en cours
                        </h3>
                        <p class="text-sm text-red-700 mb-1">
                            Raison : <strong>{{ ['work_not_delivered' => 'Travail non livré', 'work_not_conform' => 'Travail non conforme', 'poor_quality' => 'Mauvaise qualité', 'late_delivery' => 'Livraison en retard', 'payment_issue' => 'Problème de paiement', 'other' => 'Autre'][$order->dispute->reason] ?? $order->dispute->reason }}</strong>
                        </p>
                        <p class="text-sm text-red-700">
                            {{ $order->dispute->description }}
                        </p>
                        <p class="text-xs text-red-500 mt-3">
                            Notre équipe examine la situation. La commande reste bloquée jusqu'à résolution.
                        </p>
                    </div>
                    @endif

                    {{-- Section Avis - À ajouter dans orders/show.blade.php après la section chat --}}

                    @if($order->status === 'completed')
                    {{-- Vérifier si un avis existe --}}
                    @php
                    $userReview = \App\Models\Review::where('order_id', $order->id)
                    ->where('reviewer_id', Auth::id())
                    ->first();
                    @endphp

                    @if($userReview)
                    {{-- Afficher l'avis laissé --}}
                    <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6">Votre avis</h2>
                        <x-review-card :review="$userReview" />
                    </div>
                    @else
                    {{-- CTA pour laisser un avis --}}
                    <div class="bg-terracotta-600 rounded-xl p-8 text-center">
                        <h3 class="text-2xl font-serif font-medium text-cream-50 mb-4"><x-app-icon name="star" class="w-6 h-6 inline-block" /> Laisser un avis</h3>
                        <p class="text-cream-50/90 mb-6">
                            Partagez votre expérience pour aider la communauté !
                        </p>
                        <a href="{{ route('reviews.create', $order) }}"
                            class="inline-block bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md">
                            @if($userRole === 'client')
                            Évaluer le prestataire
                            @else
                            Évaluer le client
                            @endif
                        </a>
                    </div>
                    @endif
                    @endif

                    {{-- CHAT INTÉGRÉ --}}
                    @if(in_array($order->status, ['paid', 'in_progress', 'delivered', 'completed']))
                    <livewire:order-chat :order="$order" />
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Info Client/Prestataire --}}
                    <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                        <h3 class="text-lg font-bold text-ink-900 mb-4">
                            {{ $userRole === 'prestataire' ? 'Client' : 'Prestataire' }}
                        </h3>

                        @php
                        $otherUser = $userRole === 'prestataire' ? $order->client : $order->prestataire;
                        @endphp

                        <div class="text-center mb-4">
                            <img src="{{ $otherUser->avatar ? Storage::url($otherUser->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) }}"
                                alt="{{ $otherUser->name }}"
                                class="w-20 h-20 rounded-full mx-auto mb-3 border-4 border-ink-100">
                            @if($userRole === 'prestataire' && $otherUser->isClient())
                                <a href="{{ route('client.profile', $otherUser->slug) }}" class="font-bold text-ink-900 hover:text-terracotta-600 transition">{{ $otherUser->name }}</a>
                            @else
                                <p class="font-bold text-ink-900">{{ $otherUser->name }}</p>
                            @endif
                            <p class="text-sm text-ink-400">{{ $otherUser->city ?? 'Bénin' }}</p>

                            @if($otherUser->total_reviews > 0)
                            <div class="flex items-center justify-center gap-1 mt-2">
<x-app-icon name="star" class="w-4 h-4 inline-block text-ochre-500" />
                                <span class="font-bold">{{ number_format($otherUser->rating, 1) }}</span>
                                <span class="text-ink-400 text-sm">({{ $otherUser->total_reviews }} avis)</span>
                            </div>
                            @endif
                        </div>

                        {{-- Coordonnées visibles uniquement une fois la commande payée (même garde que le chat) :
                             sinon une commande non payée suffirait à récolter email/téléphone des prestataires. --}}
                        @if(in_array($order->status, ['paid', 'in_progress', 'delivered', 'completed']))
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-ink-500">
                                <x-app-icon name="envelope" class="w-4 h-4 inline-block" />
                                <span>{{ $otherUser->email }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-ink-500">
                                <x-app-icon name="phone" class="w-4 h-4 inline-block" />
                                <span>{{ $otherUser->phone }}</span>
                            </div>
                        </div>
                        @endif

                        @if($userRole === 'client')
                        <a href="{{ route('prestataire.profile', $otherUser->slug) }}"
                            class="mt-4 block text-center bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-4 py-3 rounded-xl transition">
                            Voir le profil
                        </a>
                        @endif
                    </div>

                    {{-- Détails paiement --}}
                    <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                        <h3 class="text-lg font-bold text-ink-900 mb-4">Détails de paiement</h3>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-ink-500">Prix service</span>
                                <span class="font-bold">{{ number_format($order->amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-500">Frais de service ({{ number_format(\App\Models\Order::CLIENT_FEE_RATE * 100, 0, ',', ' ') }}%, client)</span>
                                <span class="font-bold">{{ number_format($order->client_fee, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="font-bold text-ink-900">Total payé par le client</span>
                                <span class="text-2xl font-bold text-ink-900">{{ number_format($order->total_charged, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @if($userRole === 'prestataire')
                                <div class="border-t pt-3 flex justify-between text-sm">
                                    <span class="text-ink-500">Commission Azohub ({{ round($order->commission / max((float) $order->amount, 1) * 100) }}%, prestataire)</span>
                                    <span class="font-semibold text-ink-700">-{{ number_format($order->commission, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-bold text-ink-900">Vous recevrez</span>
                                    <span class="text-xl font-bold text-forest-700">{{ number_format($order->prestataire_amount, 0, ',', ' ') }} FCFA</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between text-sm">
                                <span class="text-ink-500">Statut paiement</span>
                                <span class="font-bold
                                    @if($order->payment_status === 'released') text-forest-700
                                    @elseif($order->payment_status === 'held') text-ochre-600
                                    @elseif($order->payment_status === 'refund_pending') text-ochre-600
                                    @elseif($order->payment_status === 'refunded') text-red-600
                                    @else text-ink-500
                                    @endif">
                                    {{ $order->payment_status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Refuser (Prestataire) --}}
    <div id="refuse-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-cream-50 rounded-xl p-8 max-w-md mx-4">
            <h3 class="text-2xl font-serif font-medium text-ink-900 mb-4">Refuser la commande</h3>
            <p class="text-ink-500 mb-6">
                Êtes-vous sûr de vouloir refuser cette commande ? Le client sera remboursé.
            </p>

            <form action="{{ route('orders.refuse', $order) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Raison (optionnel)
                    </label>
                    <textarea name="refusal_reason" rows="3"
                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-red-600 focus:ring-4 focus:ring-red-100 transition"
                        placeholder="Expliquez pourquoi vous refusez..."></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="button"
                        onclick="document.getElementById('refuse-modal').classList.add('hidden')"
                        class="flex-1 bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-6 py-3 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-lg transition">
                        Confirmer le refus
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Révision (Client) --}}
    <div id="revision-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-cream-50 rounded-xl p-8 max-w-md mx-4">
            <h3 class="text-2xl font-serif font-medium text-ink-900 mb-4">Demander une révision</h3>
            <p class="text-ink-500 mb-6">
                Expliquez ce qui doit être modifié ou amélioré.
            </p>

            <form action="{{ route('orders.request-revision', $order) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Détails de la révision <span class="text-red-500">*</span>
                    </label>
                    <textarea name="revision_notes" rows="4" required
                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-ochre-600 focus:ring-4 focus:ring-ochre-500/15 transition"
                        placeholder="Décrivez précisément ce qui doit être corrigé..."></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="button"
                        onclick="document.getElementById('revision-modal').classList.add('hidden')"
                        class="flex-1 bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-6 py-3 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 bg-ochre-600 hover:bg-ochre-500 text-white font-bold px-6 py-3 rounded-lg transition">
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Litige --}}
    <div id="dispute-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-cream-50 rounded-xl p-8 max-w-md mx-4">
            <h3 class="text-2xl font-serif font-medium text-ink-900 mb-4">Signaler un litige</h3>
            <p class="text-ink-500 mb-6">
                Notre équipe examinera la situation. La commande sera bloquée en attendant la résolution.
            </p>

            <form action="{{ route('orders.dispute.store', $order) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Raison <span class="text-red-500">*</span>
                    </label>
                    <select name="reason" required
                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-red-500 focus:ring-4 focus:ring-red-100 transition">
                        <option value="">Sélectionnez une raison...</option>
                        <option value="work_not_delivered">Travail non livré</option>
                        <option value="work_not_conform">Travail non conforme</option>
                        <option value="poor_quality">Mauvaise qualité</option>
                        <option value="late_delivery">Livraison en retard</option>
                        <option value="payment_issue">Problème de paiement</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" rows="4" required minlength="20"
                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-red-500 focus:ring-4 focus:ring-red-100 transition"
                        placeholder="Décrivez précisément le problème (min. 20 caractères)..."></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Preuves <span class="text-ink-400 font-normal">(optionnel — photos, captures d'écran...)</span>
                    </label>
                    <input type="file" name="evidences[]" multiple accept="image/*,.pdf"
                        class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-red-500 focus:ring-4 focus:ring-red-100 transition text-sm">
                    <p class="text-xs text-ink-400 mt-1">5 fichiers max, 5 MB chacun.</p>
                </div>

                <div class="flex gap-4">
                    <button type="button"
                        onclick="document.getElementById('dispute-modal').classList.add('hidden')"
                        class="flex-1 bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-6 py-3 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-lg transition">
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($order->status === 'pending_payment')
    {{-- La confirmation FedaPay arrive de façon asynchrone (webhook), après le retour du
         client sur cette page : sans ce sondage, le statut "En attente de paiement" reste
         affiché tel quel jusqu'à ce que le client recharge lui-même la page, ce qui donne
         l'impression que rien ne s'est passé alors que le paiement est simplement en cours
         de traitement côté serveur. --}}
    <script>
        (function () {
            const statusUrl = @json(route('orders.status', $order));
            let attempts = 0;
            const maxAttempts = 40; // ~4 minutes à 6s d'intervalle

            const poll = setInterval(async () => {
                attempts++;
                if (attempts > maxAttempts) {
                    clearInterval(poll);
                    return;
                }
                try {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    if (data.status && data.status !== 'pending_payment') {
                        clearInterval(poll);
                        window.location.reload();
                    }
                } catch (e) {
                    // Silencieux : une requête ratée n'empêche pas la suivante.
                }
            }, 6000);
        })();
    </script>
    @endif
</x-app-layout>