<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2"><x-app-icon name="cog" class="w-8 h-8 inline-block" /> Paramètres du compte</h1>
                <p class="text-gray-600">Gérez vos informations personnelles et préférences</p>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                    <p class="text-green-700 font-semibold"><x-app-icon name="check-circle" class="w-5 h-5 inline-block align-text-bottom" /> {{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <p class="text-red-700 font-semibold"><x-app-icon name="x-circle" class="w-5 h-5 inline-block align-text-bottom" /> {{ session('error') }}</p>
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Sidebar Navigation --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl p-6 shadow-lg sticky top-8">
                        <div class="text-center mb-6 pb-6 border-b">
                            <div class="relative inline-block mb-4">
                                <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200' }}" 
                                     alt="{{ $user->name }}"
                                     class="w-24 h-24 rounded-full mx-auto border-4 border-blue-100">
                                @if($user->identity_verified)
                                    <span class="absolute bottom-0 right-6 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                        <x-app-icon name="check" class="w-3 h-3 inline-block align-text-bottom" /> Vérifié
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-xl font-black text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                            @if($user->isPrestataire())
                                <span class="inline-block mt-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">
                                    <x-app-icon name="briefcase" class="w-4 h-4 inline-block align-text-bottom" /> Prestataire {{ ucfirst($user->level) }}
                                </span>
                            @else
                                <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">
                                    <x-app-icon name="user" class="w-4 h-4 inline-block align-text-bottom" /> Client
                                </span>
                            @endif
                        </div>

                        <nav class="space-y-2">
                            <a href="#informations" class="block px-4 py-3 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-blue-900 font-semibold transition">
                                <x-app-icon name="user" class="w-4 h-4 inline-block align-text-bottom" /> Informations personnelles
                            </a>
                            <a href="#password" class="block px-4 py-3 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-blue-900 font-semibold transition">
                                <x-app-icon name="lock" class="w-4 h-4 inline-block align-text-bottom" /> Mot de passe
                            </a>
                            @if($user->isPrestataire())
                                <a href="#prestataire" class="block px-4 py-3 rounded-xl hover:bg-blue-50 text-gray-700 hover:text-blue-900 font-semibold transition">
                                    <x-app-icon name="briefcase" class="w-4 h-4 inline-block align-text-bottom" /> Infos prestataire
                                </a>
                            @endif
                            <a href="#delete" class="block px-4 py-3 rounded-xl hover:bg-red-50 text-red-600 hover:text-red-700 font-semibold transition">
                                <x-app-icon name="trash" class="w-4 h-4 inline-block align-text-bottom" /> Supprimer le compte
                            </a>
                        </nav>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Informations personnelles --}}
                    <div id="informations" class="bg-white rounded-3xl p-8 shadow-lg">
                        <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="user" class="w-6 h-6 inline-block" /> Informations personnelles</h2>
                        
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            {{-- Avatar --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Photo de profil</label>
                                <div class="flex items-center gap-4">
                                    <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200' }}" 
                                         alt="{{ $user->name }}"
                                         id="avatar-preview"
                                         class="w-20 h-20 rounded-full border-2 border-gray-200">
                                    <div class="flex-1">
                                        <input type="file" 
                                               name="avatar" 
                                               id="avatar"
                                               accept="image/*"
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <p class="text-xs text-gray-500 mt-1">JPG, PNG ou GIF. Max 2MB.</p>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                                        Nom complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                                        Adresse e-mail <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="phone" 
                                           value="{{ old('phone', $user->phone) }}" 
                                           required
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>

                                {{-- City --}}
                                <div>
                                    <label for="city" class="block text-sm font-bold text-gray-700 mb-2">
                                        Ville <span class="text-red-500">*</span>
                                    </label>
                                    <select name="city" 
                                            id="city" 
                                            required
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                        <option value="">Sélectionnez votre ville</option>
                                        @foreach(config('communes') as $departement => $villes)
                                            <optgroup label="{{ $departement }}">
                                                @foreach($villes as $ville)
                                                    <option value="{{ $ville }}" {{ old('city', $user->city) === $ville ? 'selected' : '' }}>
                                                        {{ $ville }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" 
                                        class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-full transition transform hover:scale-105">
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Informations prestataire --}}
                    @if($user->isPrestataire())
                        <div id="prestataire" class="bg-white rounded-3xl p-8 shadow-lg">
                            <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="briefcase" class="w-6 h-6 inline-block" /> Informations prestataire</h2>
                            
                            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                                @csrf
                                @method('PATCH')

                                {{-- Bio --}}
                                <div>
                                    <label for="bio" class="block text-sm font-bold text-gray-700 mb-2">
                                        Biographie
                                    </label>
                                    <textarea name="bio" 
                                              id="bio" 
                                              rows="4"
                                              maxlength="500"
                                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">{{ old('bio', $user->bio) }}</textarea>
                                    <p class="text-xs text-gray-500 mt-1">{{ strlen($user->bio ?? '') }}/500 caractères</p>
                                    <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                                </div>

                                {{-- Availability --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        Disponibilité
                                    </label>
                                    <div class="grid grid-cols-3 gap-4">
                                        <label class="relative cursor-pointer">
                                            <input type="radio" 
                                                   name="availability" 
                                                   value="disponible" 
                                                   {{ old('availability', $user->availability) === 'disponible' ? 'checked' : '' }}
                                                   class="peer sr-only">
                                            <div class="border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 rounded-xl p-4 text-center transition">
                                                <div class="text-2xl mb-1"><x-app-icon name="check-circle" class="w-6 h-6 inline-block" /></div>
                                                <div class="font-bold text-sm">Disponible</div>
                                            </div>
                                        </label>

                                        <label class="relative cursor-pointer">
                                            <input type="radio" 
                                                   name="availability" 
                                                   value="occupe" 
                                                   {{ old('availability', $user->availability) === 'occupe' ? 'checked' : '' }}
                                                   class="peer sr-only">
                                            <div class="border-2 border-gray-200 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 rounded-xl p-4 text-center transition">
                                                <div class="text-2xl mb-1"><x-app-icon name="cog" class="w-6 h-6 inline-block" /></div>
                                                <div class="font-bold text-sm">Occupé</div>
                                            </div>
                                        </label>

                                        <label class="relative cursor-pointer">
                                            <input type="radio" 
                                                   name="availability" 
                                                   value="indisponible" 
                                                   {{ old('availability', $user->availability) === 'indisponible' ? 'checked' : '' }}
                                                   class="peer sr-only">
                                            <div class="border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 rounded-xl p-4 text-center transition">
                                                <div class="text-2xl mb-1"><x-app-icon name="x-circle" class="w-6 h-6 inline-block" /></div>
                                                <div class="font-bold text-sm">Indisponible</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <button type="submit" 
                                            class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-full transition transform hover:scale-105">
                                        Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- Mot de passe --}}
                    <div id="password" class="bg-white rounded-3xl p-8 shadow-lg">
                        <h2 class="text-2xl font-black text-gray-900 mb-6"><x-app-icon name="lock" class="w-6 h-6 inline-block" /> Modifier le mot de passe</h2>
                        
                        <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="current_password" class="block text-sm font-bold text-gray-700 mb-2">
                                    Mot de passe actuel <span class="text-red-500">*</span>
                                </label>
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       required
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                                        Nouveau mot de passe <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           required
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">
                                        Confirmer le mot de passe <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation" 
                                           required
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" 
                                        class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-full transition transform hover:scale-105">
                                    Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Supprimer le compte --}}
                    <div id="delete" class="bg-red-50 rounded-3xl p-8 shadow-lg border-2 border-red-200">
                        <h2 class="text-2xl font-black text-red-900 mb-4"><x-app-icon name="trash" class="w-6 h-6 inline-block" /> Supprimer le compte</h2>
                        <p class="text-red-700 mb-6">
                            Une fois votre compte supprimé, toutes vos données seront définitivement effacées. 
                            Cette action est irréversible.
                        </p>
                        
                        <button type="button"
                                onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-full transition">
                            Supprimer mon compte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmation suppression --}}
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-8 max-w-md mx-4">
            <h3 class="text-2xl font-black text-gray-900 mb-4"><x-app-icon name="exclamation-triangle" class="w-6 h-6 inline-block" /> Confirmer la suppression</h3>
            <p class="text-gray-700 mb-6">
                Êtes-vous sûr de vouloir supprimer votre compte ? 
                Cette action est définitive et irréversible.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="mb-6">
                    <label for="delete_password" class="block text-sm font-bold text-gray-700 mb-2">
                        Entrez votre mot de passe pour confirmer
                    </label>
                    <input type="password" 
                           name="password" 
                           id="delete_password" 
                           required
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-red-600 focus:ring-4 focus:ring-red-100 transition">
                </div>

                <div class="flex gap-4">
                    <button type="button"
                            onclick="document.getElementById('delete-modal').classList.add('hidden')"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-6 py-3 rounded-full transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-full transition">
                        Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Preview avatar
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    @endpush
</x-app-layout>