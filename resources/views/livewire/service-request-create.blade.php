<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-3xl">
        <nav class="mb-6 text-sm">
            <ol class="flex items-center gap-2 text-ink-500">
                <li><a href="{{ route('home') }}" class="hover:text-terracotta-600">Accueil</a></li>
                <li>→</li>
                <li><a href="{{ route('service-requests.index') }}" class="hover:text-terracotta-600">Demandes</a></li>
                <li>→</li>
                <li class="text-ink-900 font-semibold">Nouvelle demande</li>
            </ol>
        </nav>

        <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
            <h1 class="text-2xl font-serif font-medium text-ink-900 mb-2">Publier une demande</h1>
            <p class="text-ink-500 mb-8">
                Décrivez votre besoin, recevez des propositions de plusieurs prestataires et choisissez la meilleure offre.
            </p>

            <form wire:submit.prevent="submit" class="space-y-6">
                {{-- Catégorie --}}
                <div>
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Catégorie <span class="text-red-500">*</span>
                    </label>
                    {{-- pr-10 : voir services-index.blade.php (BUG040), même risque de
                         chevauchement texte/flèche avec le plugin @tailwindcss/forms. --}}
                    <select wire:model="categoryId" class="w-full pl-4 pr-10 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        <option value="">Choisir une catégorie...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Titre --}}
                <div>
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Titre de votre demande <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="title" placeholder="Ex : Rénovation d'une salle de bain de 8m²"
                           class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                    @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-bold text-ink-700 mb-2">
                        Description détaillée <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="description" rows="6"
                              placeholder="Décrivez précisément votre besoin : ce que vous attendez, les contraintes, le contexte..."
                              class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition resize-none"></textarea>
                    @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-ink-300 mt-1">{{ strlen($description) }}/2000 caractères (min. 20)</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    {{-- Budget --}}
                    <div>
                        <label class="block text-sm font-bold text-ink-700 mb-2">Budget indicatif (FCFA)</label>
                        <input type="number" wire:model="budget" placeholder="Optionnel"
                               class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        @error('budget') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Délai --}}
                    <div>
                        <label class="block text-sm font-bold text-ink-700 mb-2">Délai souhaité (jours)</label>
                        <input type="number" wire:model="deadline" placeholder="Optionnel"
                               class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        @error('deadline') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    {{-- Ville --}}
                    <div>
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Ville <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="city" placeholder="Ex : Cotonou"
                               class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        @error('city') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Adresse --}}
                    <div>
                        <label class="block text-sm font-bold text-ink-700 mb-2">Adresse précise</label>
                        <input type="text" wire:model="address" placeholder="Optionnel"
                               class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        @error('address') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Pièces jointes --}}
                <div>
                    <label class="block text-sm font-bold text-ink-700 mb-2">Photos / documents</label>
                    <input type="file" wire:model="attachments" multiple
                           class="w-full px-4 py-3 border-2 border-dashed border-ink-200 rounded-xl focus:border-terracotta-600 transition text-sm">
                    <div wire:loading wire:target="attachments" class="text-sm text-ink-400 mt-1">Chargement...</div>
                    @error('attachments.*') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    @if(!empty($attachments))
                        <ul class="mt-2 space-y-1">
                            @foreach($attachments as $file)
                                <li class="text-sm text-ink-500">📎 {{ $file->getClientOriginalName() }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-ink-100">
                    <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submit">Publier ma demande</span>
                        <span wire:loading wire:target="submit">Publication...</span>
                    </button>
                    <a href="{{ route('service-requests.index') }}" class="text-sm text-ink-400 hover:text-ink-700 font-semibold">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
