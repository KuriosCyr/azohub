<div>
    @if($review->response)
        <div class="mt-3 pl-4 border-l-2 border-terracotta-200 bg-terracotta-50/40 rounded-r-lg p-3">
            <p class="text-xs font-bold text-terracotta-700 mb-1">
                Réponse du prestataire · {{ $review->responded_at->diffForHumans() }}
            </p>
            <p class="text-sm text-ink-700 leading-relaxed whitespace-pre-wrap">{{ $review->response }}</p>
        </div>
    @elseif($this->canRespond)
        @if(!$showForm)
            <button type="button" wire:click="toggleForm" class="mt-2 text-xs font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                + Répondre à cet avis
            </button>
        @else
            <form wire:submit.prevent="submit" class="mt-3 space-y-2">
                <textarea wire:model="text" rows="3" maxlength="1000"
                          placeholder="Votre réponse, visible publiquement sous l'avis. Elle ne pourra plus être modifiée."
                          class="w-full px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-terracotta-600 focus:ring-2 focus:ring-terracotta-50 transition"></textarea>
                @error('text')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold text-xs px-4 py-2 rounded-lg transition">
                        Publier la réponse
                    </button>
                    <button type="button" wire:click="toggleForm" class="text-ink-400 hover:text-ink-600 text-xs font-bold transition">
                        Annuler
                    </button>
                </div>
            </form>
        @endif
    @endif
</div>
