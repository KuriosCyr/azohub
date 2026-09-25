<x-app-layout>
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('prestataire.services.index') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold mb-4 inline-block">
                    ← Retour à mes services
                </a>
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="pencil-square" class="w-8 h-8 inline-block" /> Modifier le service</h1>
                <p class="text-ink-500">{{ $service->title }}</p>
                @if($service->status === 'rejected')
                    <p class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"><strong>Service refusé.</strong> {{ $service->moderation_note ?: 'Aucun motif précisé.' }}</p>
                @elseif($service->status === 'pending')
                    <p class="mt-3 rounded-lg bg-ochre-500/15 px-4 py-3 text-sm text-ink-700">Ce service est en cours de modération.</p>
                @endif
                <p class="mt-2 text-xs text-ink-400">Toute modification du contenu (texte, prix, images) est soumise à une nouvelle validation avant de réapparaître pour les clients.</p>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('prestataire.services.update', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Informations de base --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6"><x-app-icon name="clipboard" class="w-6 h-6 inline-block" /> Informations de base</h2>

                    <div class="space-y-6">
                        {{-- Catégorie --}}
                        <div>
                            <label for="category_id" class="block text-sm font-bold text-ink-700 mb-2">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" id="category_id" required
                                    class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                <option value="">Sélectionnez une catégorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        {{-- Titre --}}
                        <div>
                            <label for="title" class="block text-sm font-bold text-ink-700 mb-2">
                                Titre du service <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $service->title) }}" required
                                   class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-bold text-ink-700 mb-2">
                                Description détaillée <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" id="description" rows="6" required
                                      class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">{{ old('description', $service->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Ce qui est inclus --}}
                        <div>
                            <label for="what_included" class="block text-sm font-bold text-ink-700 mb-2">
                                Ce qui est inclus
                            </label>
                            <textarea name="what_included" id="what_included" rows="4"
                                      class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">{{ old('what_included', $service->what_included) }}</textarea>
                            <x-input-error :messages="$errors->get('what_included')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Zone d'intervention --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="map-pin" class="w-6 h-6 inline-block" /> Zone d'intervention <span class="text-red-500">*</span></h2>
                    <p class="text-sm text-ink-500 mb-6">Où pouvez-vous intervenir ? Les clients de ces communes vous trouveront en filtrant par ville. Modifier la zone ne demande pas de nouvelle validation.</p>

                    @php
                        // Voir la même remarque dans create.blade.php : sans ce garde-fou, décocher
                        // toutes les zones puis échouer sur un autre champ faisait réapparaître les
                        // anciennes zones du service, comme si le nettoyage n'avait servi à rien.
                        $defaultAreas = $errors->any() ? [] : $service->areasList();
                    @endphp
                    <x-service-areas-picker :selected="old('service_areas', $defaultAreas)" :nationwide="(bool) old('serves_nationwide', $service->serves_nationwide)" />
                    <x-input-error :messages="$errors->get('service_areas')" class="mt-2" />
                    <x-input-error :messages="$errors->get('service_areas.*')" class="mt-2" />
                </div>
                {{-- Tarification --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /> Tarification et délais</h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Prix --}}
                        <div>
                            <label for="price" class="block text-sm font-bold text-ink-700 mb-2">
                                Prix <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="price" id="price" value="{{ old('price', $service->price) }}" required min="0" step="1000"
                                       class="w-full px-4 py-3 pr-20 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 font-bold">FCFA</span>
                            </div>
                            <p class="text-xs text-ink-400 mt-1">Multiples de 1000 FCFA (ex : 15000, 25000...)</p>
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        {{-- Type de prix --}}
                        <div>
                            <label class="block text-sm font-bold text-ink-700 mb-2">
                                Type de prix <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="price_type" value="fixe" {{ old('price_type', $service->price_type) === 'fixe' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="border-2 border-ink-200 peer-checked:border-ink-900 peer-checked:bg-terracotta-50 rounded-xl p-4 text-center transition">
                                        <div class="text-2xl mb-1"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /></div>
                                        <div class="font-bold text-sm">Prix fixe</div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="price_type" value="a_partir_de" {{ old('price_type', $service->price_type) === 'a_partir_de' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="border-2 border-ink-200 peer-checked:border-ink-900 peer-checked:bg-terracotta-50 rounded-xl p-4 text-center transition">
                                        <div class="text-2xl mb-1"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /></div>
                                        <div class="font-bold text-sm">À partir de</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Délai de livraison --}}
                        <div>
                            <label for="delivery_time" class="block text-sm font-bold text-ink-700 mb-2">
                                Délai de réalisation <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="delivery_time" id="delivery_time" value="{{ old('delivery_time', $service->delivery_time) }}" required min="1" max="365"
                                       class="w-full px-4 py-3 pr-20 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 font-bold">jours</span>
                            </div>
                            <x-input-error :messages="$errors->get('delivery_time')" class="mt-2" />
                        </div>

                        {{-- Statut actif --}}
                        <div>
                            <label class="block text-sm font-bold text-ink-700 mb-2">
                                Statut du service
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-14 h-7 bg-ink-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-terracotta-600/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-ink-200 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-forest-600"></div>
                                <span class="ml-3 text-sm font-medium text-ink-900">Service {{ old('is_active', $service->is_active) ? 'activé' : 'désactivé' }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Images --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6"><x-app-icon name="camera" class="w-6 h-6 inline-block" /> Images</h2>

                    {{-- Image actuelle --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-ink-700 mb-2">
                            Image de couverture actuelle
                        </label>
                        @if($service->cover_image)
                            <div class="mb-4 max-w-md h-56 rounded-lg shadow-sm overflow-hidden">
                                <x-service-cover :service="$service" class="w-full h-full object-cover" />
                            </div>
                        @endif

                        <label for="cover_image" class="block text-sm font-bold text-ink-700 mb-2">
                            Changer l'image de couverture (optionnel)
                        </label>
                        <input type="file" name="cover_image" id="cover_image" accept="image/*"
                               class="w-full text-sm text-ink-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-terracotta-50 file:text-terracotta-700 hover:file:bg-terracotta-100"
                               onchange="previewImage(this, 'cover_preview')">
                        <div id="cover_preview" class="mt-4"></div>
                        <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                    </div>

                    {{-- Portfolio --}}
                    <div>
                        <label for="portfolio" class="block text-sm font-bold text-ink-700 mb-2">
                            Ajouter des images au portfolio (optionnel)
                        </label>
                        <input type="file" name="portfolio[]" id="portfolio" accept="image/*" multiple
                               class="w-full text-sm text-ink-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-ink-100/30 file:text-ink-700 hover:file:bg-ink-100/50"
                               onchange="previewMultipleImages(this, 'portfolio_preview')">
                        <div id="portfolio_preview" class="grid grid-cols-3 gap-4 mt-4"></div>
                    </div>
                </div>

                {{-- Tags --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6"><x-app-icon name="tag" class="w-6 h-6 inline-block" /> Tags</h2>

                    <div>
                        <label for="tags" class="block text-sm font-bold text-ink-700 mb-2">
                            Mots-clés
                        </label>
                        @php
                            $currentTags = is_array($service->tags) ? implode(', ', $service->tags) : '';
                        @endphp
                        <input type="text" name="tags" id="tags" value="{{ old('tags', $currentTags) }}"
                               placeholder="plomberie, urgent, weekend, nuit"
                               class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        <p class="text-xs text-ink-400 mt-1">Séparez les mots-clés par des virgules</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-4">
                    <a href="{{ route('prestataire.services.index') }}"
                       class="flex-1 text-center bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-8 py-4 rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="flex-1 bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md">
                        <x-app-icon name="save" class="w-5 h-5 inline-block align-text-bottom" /> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="max-w-md rounded-lg shadow-lg">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        const PORTFOLIO_MAX = 5;

        // Un <input type="file" multiple> REMPLACE entièrement input.files à chaque nouvelle
        // sélection au lieu d'y ajouter : sans ce cumul manuel (stocké sur l'input lui-même),
        // choisir une 6e image effaçait les 5 précédentes au lieu de simplement refuser la 6e.
        function previewMultipleImages(input, previewId) {
            const preview = document.getElementById(previewId);
            const incoming = Array.from(input.files || []);
            const before = (input._accumulatedFiles || []).length;

            input._accumulatedFiles = (input._accumulatedFiles || []).concat(incoming).slice(0, PORTFOLIO_MAX);

            const ignored = before + incoming.length - input._accumulatedFiles.length;
            if (ignored > 0 && window.notifyAction) {
                window.notifyAction(`Maximum ${PORTFOLIO_MAX} images : ${ignored} image(s) ignorée(s).`, { icon: 'warning' });
            }

            syncPortfolioPreview(input, preview);
        }

        function syncPortfolioPreview(input, preview) {
            // Réinjecte la sélection cumulée dans l'input réel via DataTransfer : c'est ce
            // tableau (pas ce que le navigateur vient de choisir) qui part au formulaire.
            const dataTransfer = new DataTransfer();
            input._accumulatedFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;

            preview.innerHTML = '';
            input._accumulatedFiles.forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'relative';
                wrapper.innerHTML = `
                    <img class="w-full h-32 object-cover rounded-xl shadow bg-ink-100/20">
                    <button type="button" class="absolute top-1 right-1 bg-ink-900/70 hover:bg-red-600 text-cream-50 rounded-full w-6 h-6 flex items-center justify-center text-sm leading-none transition" title="Retirer cette image">&times;</button>
                `;

                const img = wrapper.querySelector('img');
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; };
                reader.readAsDataURL(file);

                wrapper.querySelector('button').addEventListener('click', () => {
                    input._accumulatedFiles.splice(index, 1);
                    syncPortfolioPreview(input, preview);
                });

                preview.appendChild(wrapper);
            });

            const counter = document.createElement('p');
            counter.className = 'text-xs text-ink-400 col-span-3 mt-1';
            counter.textContent = `${input._accumulatedFiles.length}/${PORTFOLIO_MAX} image(s) sélectionnée(s)`;
            preview.appendChild(counter);
        }
    </script>
    @endpush
</x-app-layout>