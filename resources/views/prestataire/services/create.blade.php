<x-app-layout>
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('prestataire.services.index') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold mb-4 inline-block">
                    ← Retour à mes services
                </a>
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2">+ Créer un nouveau service</h1>
                <p class="text-ink-500">Remplissez les informations pour créer votre offre de service</p>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('prestataire.services.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                   placeholder="Ex: Installation électrique complète"
                                   class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                            <p class="text-xs text-ink-400 mt-1">Soyez clair et précis (max 255 caractères)</p>
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-bold text-ink-700 mb-2">
                                Description détaillée <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" id="description" rows="6" required
                                      placeholder="Décrivez en détail votre service, votre expertise, ce qui vous différencie..."
                                      class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">{{ old('description') }}</textarea>
                            <p class="text-xs text-ink-400 mt-1">Minimum 50 caractères - Soyez détaillé et professionnel</p>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Ce qui est inclus --}}
                        <div>
                            <label for="what_included" class="block text-sm font-bold text-ink-700 mb-2">
                                Ce qui est inclus
                            </label>
                            <textarea name="what_included" id="what_included" rows="4"
                                      placeholder="Ex:
- Matériel professionnel fourni
- Déplacement dans un rayon de 20km
- Garantie 1 an sur les travaux
- Nettoyage après intervention"
                                      class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">{{ old('what_included') }}</textarea>
                            <p class="text-xs text-ink-400 mt-1">Listez ce qui est compris dans votre service</p>
                            <x-input-error :messages="$errors->get('what_included')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Zone d'intervention --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="map-pin" class="w-6 h-6 inline-block" /> Zone d'intervention <span class="text-red-500">*</span></h2>
                    <p class="text-sm text-ink-500 mb-6">Où pouvez-vous intervenir ? Les clients de ces communes vous trouveront en filtrant par ville. Modifier la zone ne demande pas de nouvelle validation.</p>

                    <x-service-areas-picker :selected="collect(old('service_areas', array_merge([Auth::user()->city], (array) Auth::user()->service_areas)))->filter()->unique()->values()->all()" :nationwide="(bool) old('serves_nationwide', false)" />
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
                                <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="1000"
                                       placeholder="25000"
                                       class="w-full px-4 py-3 pr-20 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 font-bold">FCFA</span>
                            </div>
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        {{-- Type de prix --}}
                        <div>
                            <label class="block text-sm font-bold text-ink-700 mb-2">
                                Type de prix <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="price_type" value="fixe" {{ old('price_type', 'fixe') === 'fixe' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="border-2 border-ink-200 peer-checked:border-ink-900 peer-checked:bg-terracotta-50 rounded-xl p-4 text-center transition">
                                        <div class="text-2xl mb-1"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /></div>
                                        <div class="font-bold text-sm">Prix fixe</div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="price_type" value="a_partir_de" {{ old('price_type') === 'a_partir_de' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="border-2 border-ink-200 peer-checked:border-ink-900 peer-checked:bg-terracotta-50 rounded-xl p-4 text-center transition">
                                        <div class="text-2xl mb-1"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /></div>
                                        <div class="font-bold text-sm">À partir de</div>
                                    </div>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('price_type')" class="mt-2" />
                        </div>

                        {{-- Délai de livraison --}}
                        <div>
                            <label for="delivery_time" class="block text-sm font-bold text-ink-700 mb-2">
                                Délai de réalisation <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="delivery_time" id="delivery_time" value="{{ old('delivery_time', 7) }}" required min="1" max="365"
                                       class="w-full px-4 py-3 pr-20 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-ink-400 font-bold">jours</span>
                            </div>
                            <p class="text-xs text-ink-400 mt-1">Temps estimé pour réaliser le service</p>
                            <x-input-error :messages="$errors->get('delivery_time')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Images --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6"><x-app-icon name="camera" class="w-6 h-6 inline-block" /> Images</h2>

                    {{-- Image principale --}}
                    <div class="mb-6">
                        <label for="cover_image" class="block text-sm font-bold text-ink-700 mb-2">
                            Image de couverture <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-ink-200 rounded-xl p-8 text-center hover:border-terracotta-600 transition">
                            {{-- Pas de "required" natif ici : sur un champ fichier caché (class="hidden"), le
                                 navigateur ne peut pas afficher sa bulle de validation et bloque l'envoi du
                                 formulaire en silence, sans aucun message ni rechargement de page. La validation
                                 côté serveur (ServiceController::store) prend déjà le relais avec un message
                                 visible ci-dessous si l'image manque. --}}
                            <input type="file" name="cover_image" id="cover_image" accept="image/*"
                                   class="hidden" onchange="previewImage(this, 'cover_preview')">
                            <div id="cover_preview" class="mb-4"></div>
                            <button type="button" onclick="document.getElementById('cover_image').click()"
                                    class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-6 py-3 rounded-lg transition">
                                <x-app-icon name="folder" class="w-5 h-5 inline-block align-text-bottom" /> Choisir une image
                            </button>
                            <p class="text-xs text-ink-400 mt-2">JPG, PNG ou GIF. Max 5MB. Recommandé: 800x600px</p>
                        </div>
                        <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                    </div>

                    {{-- Images portfolio --}}
                    <div>
                        <label for="portfolio" class="block text-sm font-bold text-ink-700 mb-2">
                            Portfolio (optionnel)
                        </label>
                        <div class="border-2 border-dashed border-ink-200 rounded-xl p-8 text-center hover:border-terracotta-600 transition">
                            <input type="file" name="portfolio[]" id="portfolio" accept="image/*" multiple
                                   class="hidden" onchange="previewMultipleImages(this, 'portfolio_preview')">
                            <div id="portfolio_preview" class="grid grid-cols-3 gap-4 mb-4"></div>
                            <button type="button" onclick="document.getElementById('portfolio').click()"
                                    class="bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-6 py-3 rounded-lg transition">
                                <x-app-icon name="photo" class="w-5 h-5 inline-block align-text-bottom" /> Ajouter des images (max 5)
                            </button>
                            <p class="text-xs text-ink-400 mt-2">Ajoutez jusqu'à 5 photos de vos réalisations</p>
                        </div>
                        <x-input-error :messages="$errors->get('portfolio.*')" class="mt-2" />
                    </div>
                </div>

                {{-- Tags --}}
                <div class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                    <h2 class="text-2xl font-serif font-medium text-ink-900 mb-6"><x-app-icon name="tag" class="w-6 h-6 inline-block" /> Tags (optionnel)</h2>

                    <div>
                        <label for="tags" class="block text-sm font-bold text-ink-700 mb-2">
                            Mots-clés
                        </label>
                        <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
                               placeholder="plomberie, urgent, weekend, nuit"
                               class="w-full px-4 py-3 border-2 border-ink-200 rounded-xl focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        <p class="text-xs text-ink-400 mt-1">Séparez les mots-clés par des virgules</p>
                        <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-4">
                    <a href="{{ route('prestataire.services.index') }}"
                       class="flex-1 text-center bg-ink-100/30 hover:bg-ink-100/50 text-ink-700 font-bold px-8 py-4 rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="flex-1 bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-8 py-4 rounded-lg transition shadow-md">
                        Publier le service
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
                    preview.innerHTML = `<img src="${e.target.result}" class="max-w-md mx-auto rounded-lg shadow-lg">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewMultipleImages(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';

            if (input.files) {
                const files = Array.from(input.files).slice(0, 5);
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML += `<img src="${e.target.result}" class="w-full h-32 object-cover rounded-xl shadow">`;
                    };
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
    @endpush
</x-app-layout>