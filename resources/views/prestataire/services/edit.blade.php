<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4 max-w-4xl">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('prestataire.services.index') }}" class="text-blue-900 hover:text-blue-700 font-bold mb-4 inline-block">
                    ← Retour à mes services
                </a>
                <h1 class="text-4xl font-black text-gray-900 mb-2"><x-app-icon name="pencil-square" class="w-8 h-8 inline-block" /> Modifier le service</h1>
                <p class="text-gray-600">{{ $service->title }}</p>
            </div>

            {{-- Formulaire --}}
            <form action="{{ route('prestataire.services.update', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Informations de base --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="clipboard" class="w-6 h-6 inline-block" /> Informations de base</h2>

                    <div class="space-y-6">
                        {{-- Catégorie --}}
                        <div>
                            <label for="category_id" class="block text-sm font-bold text-gray-700 mb-2">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" id="category_id" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
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
                            <label for="title" class="block text-sm font-bold text-gray-700 mb-2">
                                Titre du service <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', $service->title) }}" required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-700 mb-2">
                                Description détaillée <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" id="description" rows="6" required
                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">{{ old('description', $service->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Ce qui est inclus --}}
                        <div>
                            <label for="what_included" class="block text-sm font-bold text-gray-700 mb-2">
                                Ce qui est inclus
                            </label>
                            <textarea name="what_included" id="what_included" rows="4"
                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">{{ old('what_included', $service->what_included) }}</textarea>
                            <x-input-error :messages="$errors->get('what_included')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Tarification --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /> Tarification et délais</h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Prix --}}
                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-700 mb-2">
                                Prix <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="price" id="price" value="{{ old('price', $service->price) }}" required min="0" step="1000"
                                       class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">FCFA</span>
                            </div>
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        {{-- Type de prix --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Type de prix <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="price_type" value="fixe" {{ old('price_type', $service->price_type) === 'fixe' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-blue-900 peer-checked:bg-blue-50 rounded-xl p-4 text-center transition">
                                        <div class="text-2xl mb-1"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /></div>
                                        <div class="font-bold text-sm">Prix fixe</div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="price_type" value="a_partir_de" {{ old('price_type', $service->price_type) === 'a_partir_de' ? 'checked' : '' }} class="peer sr-only">
                                    <div class="border-2 border-gray-200 peer-checked:border-blue-900 peer-checked:bg-blue-50 rounded-xl p-4 text-center transition">
                                        <div class="text-2xl mb-1"><x-app-icon name="banknotes" class="w-6 h-6 inline-block" /></div>
                                        <div class="font-bold text-sm">À partir de</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Délai de livraison --}}
                        <div>
                            <label for="delivery_time" class="block text-sm font-bold text-gray-700 mb-2">
                                Délai de réalisation <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="delivery_time" id="delivery_time" value="{{ old('delivery_time', $service->delivery_time) }}" required min="1" max="365"
                                       class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">jours</span>
                            </div>
                            <x-input-error :messages="$errors->get('delivery_time')" class="mt-2" />
                        </div>

                        {{-- Statut actif --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Statut du service
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Service {{ old('is_active', $service->is_active) ? 'activé' : 'désactivé' }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Images --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="camera" class="w-6 h-6 inline-block" /> Images</h2>

                    {{-- Image actuelle --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Image de couverture actuelle
                        </label>
                        @if($service->cover_image)
                            <div class="mb-4 max-w-md h-56 rounded-2xl shadow-lg overflow-hidden">
                                <x-service-cover :service="$service" class="w-full h-full object-cover" />
                            </div>
                        @endif
                        
                        <label for="cover_image" class="block text-sm font-bold text-gray-700 mb-2">
                            Changer l'image de couverture (optionnel)
                        </label>
                        <input type="file" name="cover_image" id="cover_image" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               onchange="previewImage(this, 'cover_preview')">
                        <div id="cover_preview" class="mt-4"></div>
                        <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                    </div>

                    {{-- Portfolio --}}
                    <div>
                        <label for="portfolio" class="block text-sm font-bold text-gray-700 mb-2">
                            Ajouter des images au portfolio (optionnel)
                        </label>
                        <input type="file" name="portfolio[]" id="portfolio" accept="image/*" multiple
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100"
                               onchange="previewMultipleImages(this, 'portfolio_preview')">
                        <div id="portfolio_preview" class="grid grid-cols-3 gap-4 mt-4"></div>
                    </div>
                </div>

                {{-- Tags --}}
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="tag" class="w-6 h-6 inline-block" /> Tags</h2>

                    <div>
                        <label for="tags" class="block text-sm font-bold text-gray-700 mb-2">
                            Mots-clés
                        </label>
                        @php
                            $currentTags = $service->tags ? implode(', ', json_decode($service->tags, true)) : '';
                        @endphp
                        <input type="text" name="tags" id="tags" value="{{ old('tags', $currentTags) }}"
                               placeholder="plomberie, urgent, weekend, nuit"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                        <p class="text-xs text-gray-500 mt-1">Séparez les mots-clés par des virgules</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-4">
                    <a href="{{ route('prestataire.services.index') }}" 
                       class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-8 py-4 rounded-full transition">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="flex-1 bg-blue-900 hover:bg-blue-800 text-white font-black px-8 py-4 rounded-full transition transform hover:scale-105 shadow-lg">
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
                    preview.innerHTML = `<img src="${e.target.result}" class="max-w-md rounded-2xl shadow-lg">`;
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