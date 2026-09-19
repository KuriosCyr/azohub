<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="mb-6">
            <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">Messages</h1>
            <p class="text-ink-500">Vos échanges directs avec {{ Auth::user()->isPrestataire() ? 'vos clients' : 'des prestataires' }}.</p>
        </div>

        <div class="relative mb-6">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher une conversation..."
                   class="w-full pl-11 pr-4 py-3 rounded-lg border border-ink-200 text-sm focus:ring-2 focus:ring-terracotta-600 focus:border-transparent bg-cream-50">
        </div>

        <div class="bg-cream-50 rounded-xl border border-ink-100 divide-y divide-ink-100 overflow-hidden">
            @forelse($conversations as $conversation)
                @php
                    $other = $conversation->otherParticipant(Auth::id());
                    $lastMessage = $conversation->messages->first();
                @endphp
                <a href="{{ route('conversations.show', $conversation) }}" class="flex items-center gap-4 p-5 hover:bg-ink-100/20 transition">
                    <div class="relative flex-shrink-0">
                        <img src="{{ $other->avatar ? Storage::url($other->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($other->name) }}"
                             alt="{{ $other->name }}" class="w-12 h-12 rounded-full border border-ink-200 object-cover">
                        @if($other->isOnline())
                            <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-forest-600 border-2 border-cream-50" title="En ligne"></span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-ink-900 truncate">{{ $other->name }}</p>
                            @if($conversation->last_message_at)
                                <span class="text-xs text-ink-400 flex-shrink-0">{{ $conversation->last_message_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if($conversation->service)
                            <p class="text-xs text-terracotta-600 font-semibold truncate mt-0.5">{{ Str::limit($conversation->service->title, 40) }}</p>
                        @endif
                        <p class="text-sm text-ink-500 truncate mt-0.5">
                            @if($lastMessage)
                                @if($lastMessage->custom_offer_id)
                                    📋 Offre personnalisée envoyée
                                @elseif(filled($lastMessage->message))
                                    {{ Str::limit($lastMessage->message, 60) }}
                                @else
                                    📎 Pièce(s) jointe(s)
                                @endif
                            @else
                                Aucun message pour l'instant
                            @endif
                        </p>
                    </div>

                    @if($conversation->unread_count > 0)
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-terracotta-600 text-cream-50 text-xs font-bold flex items-center justify-center">
                            {{ $conversation->unread_count }}
                        </span>
                    @endif
                </a>
            @empty
                <div class="text-center py-16">
                    <div class="w-20 h-20 rounded-full bg-ink-100/30 flex items-center justify-center mx-auto mb-4">
                        <x-app-icon name="chat" class="w-10 h-10 text-ink-300" />
                    </div>
                    <h3 class="text-2xl font-bold text-ink-900 mb-2">{{ $search !== '' ? 'Aucun résultat' : 'Aucune conversation' }}</h3>
                    <p class="text-ink-500">
                        @if($search !== '')
                            Aucune conversation ne correspond à « {{ $search }} ».
                        @elseif(Auth::user()->isPrestataire())
                            Les clients qui vous contactent directement apparaîtront ici.
                        @else
                            Contactez un prestataire depuis son profil pour démarrer une conversation.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
