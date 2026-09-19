<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-3xl">
        <nav class="mb-6 text-sm">
            <ol class="flex items-center gap-2 text-ink-500">
                <li><a href="{{ route('conversations.index') }}" class="hover:text-terracotta-600">Messages</a></li>
                <li>→</li>
                <li class="text-ink-900 font-semibold">{{ $conversation->otherParticipant(Auth::id())->name }}</li>
            </ol>
        </nav>

        @php $other = $conversation->otherParticipant(Auth::id()); @endphp

        <div class="bg-cream-50 rounded-xl border border-ink-100 overflow-hidden flex flex-col" style="height: 70vh;">
            {{-- En-tête --}}
            <div class="flex items-center gap-4 p-5 border-b border-ink-100">
                <div class="relative flex-shrink-0">
                    <img src="{{ $other->avatar ? Storage::url($other->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($other->name) }}"
                         alt="{{ $other->name }}" class="w-11 h-11 rounded-full border border-ink-200 object-cover">
                    @if($other->isOnline())
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-forest-600 border-2 border-cream-50" title="En ligne"></span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        @if(Auth::user()->isClient())
                            <a href="{{ route('prestataire.profile', $other->slug) }}" class="font-bold text-ink-900 hover:text-terracotta-600 transition">{{ $other->name }}</a>
                        @else
                            <p class="font-bold text-ink-900">{{ $other->name }}</p>
                        @endif
                        @if($other->isOnline())
                            <span class="text-xs text-forest-700 font-semibold">En ligne</span>
                        @endif
                    </div>
                    @if($conversation->service)
                        <p class="text-xs text-ink-400">À propos de « {{ Str::limit($conversation->service->title, 40) }} »</p>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="mx-5 mt-4 bg-forest-600/10 border border-forest-600/20 text-forest-700 px-4 py-3 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Fil de discussion --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                @forelse($conversation->messages as $msg)
                    @php $isMine = $msg->sender_id === Auth::id(); @endphp
                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%]">
                            @if($msg->customOffer)
                                @php $offer = $msg->customOffer; @endphp
                                <div class="bg-cream-100 border-2 border-terracotta-600/30 rounded-xl p-5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-app-icon name="clipboard" class="w-4 h-4 text-terracotta-600" />
                                        <span class="text-xs font-bold uppercase text-terracotta-700">Offre personnalisée</span>
                                        <span class="ml-auto text-xs font-semibold px-2 py-1 rounded-full
                                            {{ $offer->status === 'pending' ? 'bg-ochre-100 text-ochre-700' : ($offer->status === 'accepted' ? 'bg-forest-600/10 text-forest-700' : 'bg-ink-100 text-ink-500') }}">
                                            {{ $offer->status === 'pending' ? 'En attente' : ($offer->status === 'accepted' ? 'Acceptée' : 'Déclinée') }}
                                        </span>
                                    </div>
                                    <p class="font-bold text-ink-900 mb-1">{{ $offer->title }}</p>
                                    <p class="text-sm text-ink-600 whitespace-pre-line mb-3">{{ $offer->description }}</p>
                                    <div class="flex items-center justify-between text-sm mb-4">
                                        <span class="text-ink-500">Livraison en {{ $offer->delivery_days }} jour(s)</span>
                                        <span class="font-bold text-ink-900 text-lg">{{ number_format($offer->price, 0) }} FCFA</span>
                                    </div>

                                    @if($offer->status === 'pending' && Auth::id() === $conversation->client_id)
                                        <div class="flex gap-3">
                                            <a href="{{ route('custom-offers.accept', $offer) }}"
                                               class="flex-1 text-center bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-2.5 rounded-lg transition text-sm">
                                                Accepter et payer
                                            </a>
                                            <button
                                                @click="confirmAction('Décliner cette offre ?', { confirmText: 'Décliner' }).then(ok => ok && $wire.declineOffer({{ $offer->id }}))"
                                                class="px-4 py-2.5 rounded-lg border border-ink-200 text-ink-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition text-sm font-semibold">
                                                Décliner
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="{{ $isMine ? 'bg-ink-900 text-cream-50' : 'bg-ink-100/40 text-ink-900' }} rounded-2xl px-4 py-3">
                                    @if(filled($msg->message))
                                        <p class="text-sm whitespace-pre-line">{{ $msg->message }}</p>
                                    @endif

                                    @if($msg->attachments)
                                        <div class="{{ filled($msg->message) ? 'mt-2' : '' }} space-y-2">
                                            @foreach($msg->attachments as $index => $file)
                                                <a href="{{ route('conversations.attachment.download', [$msg, $index]) }}"
                                                   class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $isMine ? 'bg-white bg-opacity-10 hover:bg-opacity-20' : 'bg-cream-50 hover:bg-cream-100' }} transition text-sm">
                                                    <x-app-icon name="paperclip" class="w-4 h-4 flex-shrink-0" />
                                                    <span class="truncate">{{ $file['name'] }}</span>
                                                    <span class="text-xs opacity-70 flex-shrink-0">({{ number_format($file['size'] / 1024, 1) }} KB)</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <p class="text-xs text-ink-400 mt-1 {{ $isMine ? 'text-right' : '' }}">{{ $msg->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <p class="text-ink-400">Démarrez la conversation en envoyant un message.</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulaire d'offre personnalisée (prestataire uniquement) --}}
            @if($this->isPrestataire && $showOfferForm)
                <div class="p-5 border-t border-ink-100 bg-cream-100/60 space-y-3">
                    <p class="font-bold text-ink-900 text-sm">Nouvelle offre personnalisée</p>
                    <input type="text" wire:model="offerTitle" placeholder="Titre de l'offre"
                           class="w-full px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
                    @error('offerTitle') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                    <textarea wire:model="offerDescription" rows="3" placeholder="Décrivez ce que comprend cette offre..."
                              class="w-full px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent"></textarea>
                    @error('offerDescription') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="number" wire:model="offerPrice" placeholder="Prix (FCFA)"
                                   class="w-full px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
                            @error('offerPrice') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="number" wire:model="offerDeliveryDays" placeholder="Délai (jours)"
                                   class="w-full px-4 py-2.5 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent">
                            @error('offerDeliveryDays') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="sendOffer" wire:loading.attr="disabled" wire:target="sendOffer"
                                class="flex-1 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold py-2.5 rounded-lg transition text-sm disabled:opacity-60">
                            Envoyer l'offre
                        </button>
                        <button wire:click="toggleOfferForm" type="button"
                                class="px-4 py-2.5 rounded-lg border border-ink-200 text-ink-600 hover:bg-ink-100/40 transition text-sm font-semibold">
                            Annuler
                        </button>
                    </div>
                </div>
            @endif

            {{-- Composer --}}
            <div class="p-5 border-t border-ink-100">
                @if(!empty($attachments))
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach($attachments as $index => $file)
                            <span class="inline-flex items-center gap-2 px-3 py-1 bg-terracotta-50 text-terracotta-700 rounded-full text-xs">
                                {{ is_string($file) ? $file : $file->getClientOriginalName() }}
                                <button type="button" wire:click="$set('attachments.{{ $index }}', null)" class="hover:text-red-600">
                                    <x-app-icon name="x-mark" class="w-3.5 h-3.5 inline-block" />
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
                @error('attachments.*') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                <div class="flex items-end gap-3">
                    @if($this->isPrestataire && !$showOfferForm)
                        <button wire:click="toggleOfferForm" type="button"
                                title="Envoyer une offre personnalisée"
                                class="flex-shrink-0 w-11 h-11 rounded-lg border border-ink-200 text-ink-500 hover:text-terracotta-600 hover:border-terracotta-600/40 transition flex items-center justify-center">
                            <x-app-icon name="clipboard" class="w-5 h-5" />
                        </button>
                    @endif
                    <label class="flex-shrink-0 w-11 h-11 rounded-lg border border-ink-200 text-ink-500 hover:text-terracotta-600 hover:border-terracotta-600/40 transition flex items-center justify-center cursor-pointer" title="Joindre un fichier">
                        <x-app-icon name="paperclip" class="w-5 h-5" />
                        <input type="file" wire:model="attachments" multiple class="hidden">
                    </label>
                    <div class="flex-1">
                        <textarea wire:model="message" rows="1" placeholder="Écrivez votre message..."
                                  class="w-full px-4 py-3 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent resize-none"></textarea>
                        @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button wire:click="sendMessage" wire:loading.attr="disabled" wire:target="sendMessage,attachments"
                            class="flex-shrink-0 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-5 py-3 rounded-lg transition disabled:opacity-60">
                        Envoyer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
