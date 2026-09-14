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
                        @php
                        $statusConfig = [
                        'pending_payment' => ['label' => 'En attente de paiement', 'color' => 'gray'],
                        'paid' => ['label' => 'Payée', 'color' => 'blue'],
                        'in_progress' => ['label' => 'En cours', 'color' => 'yellow'],
                        'delivered' => ['label' => 'Livrée', 'color' => 'purple'],
                        'completed' => ['label' => 'Terminée', 'color' => 'green'],
                        'cancelled' => ['label' => 'Annulée', 'color' => 'red'],
                        ];
                        $status = $statusConfig[$order->status] ?? ['label' => $order->status, 'color' => 'gray'];
                        @endphp

                        <span class="px-6 py-3 rounded-full text-lg font-bold
                            @if($status['color'] === 'green') bg-forest-600/10 text-forest-700
                            @elseif($status['color'] === 'blue') bg-terracotta-50 text-terracotta-700
                            @elseif($status['color'] === 'yellow') bg-ochre-500/15 text-ink-900
                            @elseif($status['color'] === 'purple') bg-purple-100 text-purple-800
                            @elseif($status['color'] === 'red') bg-red-100 text-red-800
                            @else bg-ink-100/30 text-ink-700
                            @endif">
                            {{ $status['label'] }}
                        </span>
                    </div>
                </div>
            </div>

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
                                <span class="px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full mb-2 inline-block">
                                    {{ $order->service->category->name }}
                                </span>
                                <h3 class="text-xl font-bold text-ink-900 mb-2">{{ $order->service->title }}</h3>
                                <p class="text-ink-500 mb-4">{{ Str::limit($order->service->description, 150) }}</p>

                                <div class="flex items-center gap-4">
                                    <div>
                                        <p class="text-sm text-ink-400">Prix</p>
                                        <p class="text-2xl font-bold text-ink-900">{{ number_format($order->amount, 0) }} FCFA</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-ink-400">Délai</p>
                                        <p class="font-bold text-ink-900">{{ $order->service->delivery_time }} jours</p>
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
                        </div>

                        <form action="{{ route('orders.pay', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="payment_method" class="block text-sm font-bold text-ink-700 mb-2">
                                    Moyen de paiement
                                </label>
                                <select name="payment_method" id="payment_method"
                                    class="w-full px-4 py-3 border-2 border-ink-200 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                    <option value="mtn_momo">MTN Mobile Money</option>
                                    <option value="moov_money">Moov Money</option>
                                    <option value="celtiis_cash">Celtiis Cash</option>
                                    <option value="card">Carte bancaire</option>
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-6 py-4 rounded-lg transition">
<x-app-icon name="card" class="w-5 h-5 inline-block" /> Payer {{ number_format($order->amount, 0, ',', ' ') }} FCFA
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
                            <p class="font-bold text-ink-900">{{ $otherUser->name }}</p>
                            <p class="text-sm text-ink-400">{{ $otherUser->city ?? 'Bénin' }}</p>

                            @if($userRole === 'client' && $otherUser->isPrestataire())
                            <div class="flex items-center justify-center gap-1 mt-2">
<x-app-icon name="star" class="w-4 h-4 inline-block text-ochre-500" />
                                <span class="font-bold">{{ number_format($otherUser->rating, 1) }}</span>
                                <span class="text-ink-400 text-sm">({{ $otherUser->total_reviews }} avis)</span>
                            </div>
                            @endif
                        </div>

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

                        @if($userRole === 'client')
                        <a href="{{ route('prestataire.profile', $otherUser->id) }}"
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
                                <span class="font-bold">{{ number_format($order->amount, 0) }} FCFA</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-500">Frais plateforme</span>
                                <span class="font-bold">{{ number_format($order->commission, 0) }} FCFA</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between">
                                <span class="font-bold text-ink-900">Total payé par le client</span>
                                <span class="text-2xl font-bold text-ink-900">{{ number_format($order->amount, 0) }} FCFA</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between text-sm">
                                <span class="text-ink-500">Statut paiement</span>
                                <span class="font-bold
                                    @if($order->payment_status === 'released') text-forest-700
                                    @elseif($order->payment_status === 'held') text-ochre-600
                                    @elseif($order->payment_status === 'refunded') text-red-600
                                    @else text-ink-500
                                    @endif">
                                    {{ ucfirst($order->payment_status) }}
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
</x-app-layout>