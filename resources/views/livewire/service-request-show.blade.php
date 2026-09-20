<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-5xl">
        <nav class="mb-6 text-sm">
            <ol class="flex items-center gap-2 text-ink-500">
                <li><a href="{{ route('home') }}" class="hover:text-terracotta-600">Accueil</a></li>
                <li>→</li>
                <li><a href="{{ route('service-requests.index') }}" class="hover:text-terracotta-600">Demandes</a></li>
                <li>→</li>
                <li class="text-ink-900 font-semibold">{{ Str::limit($serviceRequest->title, 40) }}</li>
            </ol>
        </nav>

        @if(session('success'))
            <div class="bg-forest-600/10 border-l-4 border-forest-600 p-4 mb-6 rounded">
                <p class="text-forest-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Détail de la demande --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-terracotta-50 text-terracotta-700 px-3 py-1 rounded-full text-xs font-bold uppercase">
                            {{ $serviceRequest->category->name }}
                        </span>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                            {{ $serviceRequest->status === 'open' ? 'bg-forest-600/10 text-forest-700' : ($serviceRequest->status === 'closed' ? 'bg-ink-100 text-ink-500' : 'bg-red-50 text-red-600') }}">
                            {{ $serviceRequest->status === 'open' ? 'Ouverte' : ($serviceRequest->status === 'closed' ? 'Clôturée' : 'Annulée') }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-serif font-medium text-ink-900 mb-4">{{ $serviceRequest->title }}</h1>
                    <p class="text-ink-700 whitespace-pre-line leading-relaxed mb-6">{{ $serviceRequest->description }}</p>

                    <div class="grid sm:grid-cols-3 gap-4 text-sm border-t border-ink-100 pt-6">
                        <div>
                            <p class="text-ink-400 mb-1">Ville</p>
                            <p class="font-semibold text-ink-900">{{ $serviceRequest->city }}</p>
                        </div>
                        @if($serviceRequest->budget)
                            <div>
                                <p class="text-ink-400 mb-1">Budget indicatif</p>
                                <p class="font-semibold text-ink-900">{{ number_format($serviceRequest->budget, 0) }} FCFA</p>
                            </div>
                        @endif
                        @if($serviceRequest->deadline)
                            <div>
                                <p class="text-ink-400 mb-1">Délai souhaité</p>
                                <p class="font-semibold text-ink-900">{{ $serviceRequest->deadline }} jour(s)</p>
                            </div>
                        @endif
                    </div>

                    @if(!empty($serviceRequest->attachments))
                        <div class="border-t border-ink-100 pt-6 mt-6">
                            <p class="text-sm font-bold text-ink-700 mb-3">Pièces jointes</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($serviceRequest->attachments as $index => $file)
                                    <a href="{{ route('service-requests.attachment.download', [$serviceRequest, $index]) }}" target="_blank"
                                       class="text-sm bg-ink-100/30 hover:bg-ink-100/60 px-3 py-2 rounded-lg text-ink-700 transition">
                                        📎 {{ $file['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Formulaire de proposition (prestataire) --}}
                @auth
                    @if(Auth::user()->isPrestataire() && !$this->isOwner)
                        <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                            @if($this->myProposal)
                                <h2 class="text-lg font-bold text-ink-900 mb-4">Votre proposition</h2>
                                <div class="flex items-center justify-between p-4 bg-ink-100/20 rounded-lg">
                                    <div>
                                        <p class="font-semibold text-ink-900">{{ number_format($this->myProposal->proposed_price, 0) }} FCFA</p>
                                        <p class="text-sm text-ink-500">Livraison en {{ $this->myProposal->delivery_time }} jour(s)</p>
                                    </div>
                                    <span class="text-xs font-semibold px-3 py-1.5 rounded-full
                                        {{ $this->myProposal->status === 'pending' ? 'bg-ochre-500/10 text-ochre-600' : ($this->myProposal->status === 'accepted' ? 'bg-forest-600/10 text-forest-700' : 'bg-red-50 text-red-600') }}">
                                        {{ ['pending' => 'En attente', 'accepted' => 'Acceptée', 'rejected' => 'Refusée', 'cancelled' => 'Annulée'][$this->myProposal->status] }}
                                    </span>
                                </div>
                            @elseif($serviceRequest->status === 'open')
                                <h2 class="text-lg font-bold text-ink-900 mb-4">Envoyer une proposition</h2>
                                <form wire:submit.prevent="submitProposal" class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-bold text-ink-700 mb-2">Votre message</label>
                                        <textarea wire:model="message" rows="4" placeholder="Présentez votre approche, votre expérience..."
                                                  class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition resize-none"></textarea>
                                        @error('message') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-bold text-ink-700 mb-2">Votre prix (FCFA)</label>
                                            <input type="number" wire:model="proposedPrice"
                                                   class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                            @error('proposedPrice') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-ink-700 mb-2">Délai de livraison (jours)</label>
                                            <input type="number" wire:model="deliveryTime"
                                                   class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                            @error('deliveryTime') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <button type="submit" wire:loading.attr="disabled" wire:target="submitProposal"
                                            class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-3.5 rounded-lg transition shadow-md disabled:opacity-60">
                                        <span wire:loading.remove wire:target="submitProposal">Envoyer ma proposition</span>
                                        <span wire:loading wire:target="submitProposal">Envoi...</span>
                                    </button>
                                </form>
                            @else
                                <p class="text-ink-500">Cette demande n'est plus ouverte aux propositions.</p>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>

            {{-- Sidebar : client + propositions --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                    <p class="text-xs text-ink-400 mb-2">Publiée par</p>
                    <div class="flex items-center gap-3">
                        <img src="{{ $serviceRequest->client->avatar ? Storage::url($serviceRequest->client->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($serviceRequest->client->name) }}"
                             alt="{{ $serviceRequest->client->name }}" class="w-10 h-10 rounded-full border border-ink-200">
                        <div>
                            <p class="font-semibold text-ink-900">{{ $serviceRequest->client->name }}</p>
                            <p class="text-xs text-ink-400">{{ $serviceRequest->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-cream-50 rounded-xl p-6 border border-ink-100">
                    <h2 class="text-lg font-bold text-ink-900 mb-4">
                        {{ $serviceRequest->proposals->count() }} proposition(s)
                    </h2>

                    @if($this->isOwner)
                        <div class="space-y-4">
                            @forelse($serviceRequest->proposals->sortByDesc('created_at') as $proposal)
                                <div class="border border-ink-100 rounded-lg p-4">
                                    <a href="{{ route('prestataire.profile', $proposal->prestataire->slug) }}"
                                       target="_blank"
                                       class="flex items-center gap-3 mb-3 group">
                                        <img src="{{ $proposal->prestataire->avatar ? Storage::url($proposal->prestataire->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($proposal->prestataire->name) }}"
                                             alt="{{ $proposal->prestataire->name }}" class="w-9 h-9 rounded-full border border-ink-200">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-ink-900 text-sm truncate group-hover:text-terracotta-600 transition">{{ $proposal->prestataire->name }}</p>
                                            <p class="text-xs text-ink-400 flex items-center gap-1">
                                                <x-app-icon name="star" class="w-3 h-3 text-ochre-500" />
                                                {{ number_format($proposal->prestataire->rating, 1) }} ({{ $proposal->prestataire->total_reviews }})
                                            </p>
                                        </div>
                                        <span class="text-xs font-semibold text-terracotta-600 opacity-0 group-hover:opacity-100 transition">Voir le profil →</span>
                                    </a>
                                    <div x-data="{ expanded: false }">
                                        <p class="text-sm text-ink-600 mb-1" :class="expanded ? '' : 'line-clamp-3'">{{ $proposal->message }}</p>
                                        <button type="button" @click="expanded = !expanded" class="text-xs font-semibold text-terracotta-600 hover:text-terracotta-700 mb-2" x-text="expanded ? 'Voir moins' : 'Voir plus'"></button>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mb-3">
                                        <span class="font-bold text-ink-900">{{ number_format($proposal->proposed_price, 0) }} FCFA</span>
                                        <span class="text-ink-500">{{ $proposal->delivery_time }} jour(s)</span>
                                    </div>

                                    @if($proposal->status === 'pending')
                                        <div class="flex gap-2" x-data>
                                            <a href="{{ route('service-requests.proposals.accept', [$serviceRequest, $proposal]) }}"
                                               class="flex-1 text-center bg-forest-600 hover:bg-forest-700 text-cream-50 font-semibold text-sm py-2 rounded-lg transition">
                                                Accepter
                                            </a>
                                            <button type="button"
                                                    @click="confirmAction('Refuser cette proposition ?', { confirmText: 'Refuser' }).then(ok => ok && $wire.rejectProposal({{ $proposal->id }}))"
                                                    class="flex-1 bg-cream-50 border border-ink-200 hover:border-red-300 text-ink-700 font-semibold text-sm py-2 rounded-lg transition">
                                                Refuser
                                            </button>
                                        </div>
                                    @else
                                        <span class="block text-center text-xs font-semibold px-3 py-2 rounded-lg
                                            {{ $proposal->status === 'accepted' ? 'bg-forest-600/10 text-forest-700' : 'bg-red-50 text-red-600' }}">
                                            {{ $proposal->status === 'accepted' ? 'Acceptée' : 'Refusée' }}
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-ink-400">Aucune proposition pour le moment.</p>
                            @endforelse
                        </div>
                    @else
                        <p class="text-sm text-ink-400">
                            Les propositions sont visibles uniquement par l'auteur de la demande.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
