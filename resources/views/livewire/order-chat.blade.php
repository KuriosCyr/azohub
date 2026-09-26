<div class="bg-cream-50 rounded-xl border border-ink-100 overflow-hidden" wire:poll.5s="refreshMessages">
    <div class="bg-ink-900 text-cream-50 px-6 py-4">
        <h3 class="text-xl font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Messages
        </h3>
        <p class="text-sm text-ink-300">
            Discussion avec {{ $order->client_id === Auth::id() ? $order->prestataire->name : $order->client->name }}
        </p>
    </div>

    {{-- Zone de messages --}}
    <div id="messages-container" class="h-96 overflow-y-auto p-6 space-y-4 bg-cream">
        @forelse($messages as $message)
            @php
                $isMine = $message->sender_id === Auth::id();
            @endphp

            <div wire:key="message-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="flex gap-3 max-w-[70%] {{ $isMine ? 'flex-row-reverse' : '' }}">
                    {{-- Avatar --}}
                    <img src="{{ $message->sender->avatar ? Storage::url($message->sender->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name) }}"
                         alt="{{ $message->sender->name }}"
                         class="w-10 h-10 rounded-full flex-shrink-0">

                    {{-- Message bubble --}}
                    <div>
                        <div class="px-4 py-3 rounded-lg {{ $isMine ? 'bg-ink-900 text-cream-50' : 'bg-cream-50 border border-ink-200 text-ink-900' }}">
                            <p class="text-sm whitespace-pre-wrap break-words">{{ $message->message }}</p>

                            {{-- Pièces jointes --}}
                            @if($message->attachments)
                                <div class="mt-3 space-y-2">
                                    @foreach($message->attachments as $index => $file)
                                        <a href="{{ route('messages.attachment.download', [$message, $index]) }}"
                                           class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $isMine ? 'bg-white bg-opacity-10 hover:bg-opacity-20' : 'bg-ink-100/30 hover:bg-ink-100/50' }} transition text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            <span class="truncate">{{ $file['name'] }}</span>
                                            <span class="text-xs opacity-70">({{ number_format($file['size'] / 1024, 1) }} KB)</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Timestamp --}}
                        <p class="text-xs text-ink-400 mt-1 {{ $isMine ? 'text-right' : '' }}">
                            {{ $message->created_at->format('d/m/Y à H:i') }}
                            @if($isMine && $message->is_read)
                                <span class="text-terracotta-600 inline-flex items-center gap-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <svg class="w-3 h-3 -ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Lu
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="w-16 h-16 rounded-full bg-terracotta-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-terracotta-600/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <p class="text-ink-400">Aucun message pour le moment</p>
                <p class="text-sm text-ink-300 mt-2">Envoyez le premier message pour démarrer la conversation</p>
            </div>
        @endforelse
    </div>

    {{-- Formulaire d'envoi --}}
    <form wire:submit.prevent="sendMessage" class="border-t border-ink-100 p-4 bg-cream-50" id="chat-form">
        <div class="space-y-3">
            {{-- Textarea --}}
            <div>
                <textarea
                    id="message-input"
                    wire:model.live.debounce.500ms="message"
                    rows="3"
                    placeholder="Écrivez votre message..."
                    class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition resize-none"
                    @keydown.ctrl.enter="submitMessage()"
                ></textarea>
                @error('message')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

                @if($this->containsContactInfo)
                    <div class="mt-2 flex items-start gap-2 bg-ochre-100 border border-ochre-300 text-ink-800 rounded-lg px-4 py-3 text-xs">
                        <x-app-icon name="exclamation-triangle" class="w-4 h-4 flex-shrink-0 mt-0.5 text-ochre-700" />
                        <p>Votre message semble contenir un email, un numéro ou un lien. Pour votre sécurité, échangez et payez sur Azohub — c'est ce qui vous protège en cas de litige. (Une adresse reste normale pour certains services à domicile.)</p>
                    </div>
                @endif
            </div>

            {{-- Upload fichiers --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <label class="flex items-center gap-2 cursor-pointer text-ink-500 hover:text-ink-900 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <span class="text-sm font-semibold">Joindre des fichiers</span>
                        <input type="file" wire:model="attachments" multiple class="hidden" id="file-input">
                    </label>

                    @if(!empty($attachments))
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($attachments as $index => $file)
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-terracotta-50 text-terracotta-700 rounded-full text-xs">
                                    {{ is_string($file) ? $file : $file->getClientOriginalName() }}
                                    <button type="button" wire:click="$set('attachments.{{ $index }}', null)" class="hover:text-red-600">
                                        <x-app-icon name="x-mark" class="w-4 h-4 inline-block" />
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @error('attachments.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bouton envoyer --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="sendMessage">Envoyer</span>
                    <span wire:loading wire:target="sendMessage">
                        <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Envoi...
                    </span>
                </button>
            </div>

            <p class="text-xs text-ink-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-ochre-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Astuce: Utilisez <kbd class="px-2 py-1 bg-ink-100/30 rounded text-xs">Ctrl + Entrée</kbd> pour envoyer rapidement
            </p>
        </div>
    </form>
</div>

@script
<script>
    // Fonction pour soumettre le message
    function submitMessage() {
        $wire.sendMessage();
    }

    // Auto-scroll vers le bas quand un message est envoyé
    $wire.on('message-sent', () => {
        // Vider le textarea manuellement
        const textarea = document.getElementById('message-input');
        if (textarea) {
            textarea.value = '';
        }

        // Vider l'input file
        const fileInput = document.getElementById('file-input');
        if (fileInput) {
            fileInput.value = '';
        }

        // Scroll vers le bas
        scrollToBottom();

        // Focus sur le textarea
        setTimeout(() => {
            if (textarea) {
                textarea.focus();
            }
        }, 200);
    });

    // Fonction pour scroller vers le bas
    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }
    }

    // Scroll initial vers le bas au chargement
    document.addEventListener('DOMContentLoaded', () => {
        scrollToBottom();
    });

    // Aussi après chaque mise à jour Livewire
    document.addEventListener('livewire:navigated', () => {
        scrollToBottom();
    });

    // Polling silencieux : rafraîchir automatiquement toutes les 10 secondes
    setInterval(() => {
        if (window.Livewire) {
            @this.call('refreshMessages');
        }
    }, 10000);
</script>
@endscript