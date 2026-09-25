<x-app-layout>
    <div class="min-h-screen bg-cream py-8">
        <div class="container mx-auto px-4">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-4xl font-serif font-medium text-ink-900 mb-2"><x-app-icon name="cog" class="w-8 h-8 inline-block" /> Paramètres du compte</h1>
                <p class="text-ink-500">Gérez vos informations personnelles et préférences</p>
            </div>

            {{-- Le bandeau succès/erreur global du layout s'en charge déjà (voir plus bas dans
                 le rendu) — celui-ci faisait doublon. --}}

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Sidebar Navigation --}}
                <div class="lg:col-span-1">
                    <div class="bg-cream-50 rounded-xl p-6 border border-ink-100 sticky top-8">
                        <div class="text-center mb-6 pb-6 border-b border-ink-100">
                            <div class="relative inline-block mb-4">
                                <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200' }}"
                                     alt="{{ $user->name }}"
                                     class="w-24 h-24 rounded-full mx-auto border-4 border-ink-100">
                                @if($user->identity_verified)
                                    <span class="absolute bottom-0 right-6 bg-forest-600 text-cream-50 text-xs px-2 py-1 rounded-full">
                                        <x-app-icon name="check" class="w-3 h-3 inline-block align-text-bottom" /> Vérifié
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-xl font-bold text-ink-900">{{ $user->name }}</h3>
                            <p class="text-sm text-ink-500">{{ $user->email }}</p>
                            @if($user->isPrestataire())
                                <span class="inline-block mt-2 px-3 py-1 bg-terracotta-50 text-terracotta-700 text-xs font-bold rounded-full">
                                    <x-app-icon name="briefcase" class="w-4 h-4 inline-block align-text-bottom" /> Prestataire {{ $user->level_label }}
                                </span>
                            @else
                                <span class="inline-block mt-2 px-3 py-1 bg-forest-600/10 text-forest-700 text-xs font-bold rounded-full">
                                    <x-app-icon name="user" class="w-4 h-4 inline-block align-text-bottom" /> Client
                                </span>
                            @endif
                        </div>

                        <nav class="space-y-2">
                            <a href="#informations" class="block px-4 py-3 rounded-xl hover:bg-terracotta-50 text-ink-700 hover:text-terracotta-700 font-semibold transition">
                                <x-app-icon name="user" class="w-4 h-4 inline-block align-text-bottom" /> Informations personnelles
                            </a>
                            <a href="#password" class="block px-4 py-3 rounded-xl hover:bg-terracotta-50 text-ink-700 hover:text-terracotta-700 font-semibold transition">
                                <x-app-icon name="lock" class="w-4 h-4 inline-block align-text-bottom" /> Mot de passe
                            </a>
                            @if($user->isPrestataire())
                                <a href="#prestataire" class="block px-4 py-3 rounded-xl hover:bg-terracotta-50 text-ink-700 hover:text-terracotta-700 font-semibold transition">
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
                    <div id="informations" class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-bold text-ink-900 mb-6"><x-app-icon name="user" class="w-6 h-6 inline-block" /> Informations personnelles</h2>

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('PATCH')

                            {{-- Avatar --}}
                            <div>
                                <label class="block text-sm font-bold text-ink-700 mb-2">Photo de profil</label>
                                <div class="flex items-center gap-4">
                                    <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200' }}"
                                         alt="{{ $user->name }}"
                                         id="avatar-preview"
                                         class="w-20 h-20 rounded-full border-2 border-ink-100">
                                    <div class="flex-1">
                                        <input type="file"
                                               name="avatar"
                                               id="avatar"
                                               accept="image/*"
                                               class="w-full text-sm text-ink-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-terracotta-50 file:text-terracotta-700 hover:file:bg-terracotta-600/10">
                                        <p class="text-xs text-ink-400 mt-1">JPG, PNG ou GIF. Max 2MB.</p>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-bold text-ink-700 mb-2">
                                        Nom complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           value="{{ old('name', $user->name) }}"
                                           required
                                           class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-bold text-ink-700 mb-2">
                                        Adresse e-mail <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           value="{{ old('email', $user->email) }}"
                                           required
                                           class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="block text-sm font-bold text-ink-700 mb-2">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel"
                                           name="phone"
                                           id="phone"
                                           value="{{ old('phone', $user->phone) }}"
                                           required
                                           class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>

                                {{-- City --}}
                                <div>
                                    <label for="city" class="block text-sm font-bold text-ink-700 mb-2">
                                        Ville <span class="text-red-500">*</span>
                                    </label>
                                    <select name="city"
                                            id="city"
                                            required
                                            class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
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
                                        class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-3 rounded-lg transition">
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Informations prestataire --}}
                    @if($user->isPrestataire())
                        <div id="prestataire" class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                            <h2 class="text-2xl font-bold text-ink-900 mb-6"><x-app-icon name="briefcase" class="w-6 h-6 inline-block" /> Informations prestataire</h2>

                            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                                @csrf
                                @method('PATCH')

                                {{-- Bio --}}
                                <div>
                                    <label for="bio" class="block text-sm font-bold text-ink-700 mb-2">
                                        Biographie
                                    </label>
                                    <textarea name="bio"
                                              id="bio"
                                              rows="4"
                                              maxlength="500"
                                              class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">{{ old('bio', $user->bio) }}</textarea>
                                    <p class="text-xs text-ink-400 mt-1">{{ strlen($user->bio ?? '') }}/500 caractères</p>
                                    <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                                </div>

                                {{-- Availability --}}
                                <div>
                                    <label class="block text-sm font-bold text-ink-700 mb-2">
                                        Disponibilité
                                    </label>
                                    <div class="grid grid-cols-3 gap-4">
                                        <label class="relative cursor-pointer">
                                            <input type="radio"
                                                   name="availability"
                                                   value="disponible"
                                                   {{ old('availability', $user->availability) === 'disponible' ? 'checked' : '' }}
                                                   class="peer sr-only">
                                            <div class="border-2 border-ink-100 peer-checked:border-forest-600 peer-checked:bg-forest-600/10 rounded-xl p-4 text-center transition">
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
                                            <div class="border-2 border-ink-100 peer-checked:border-ochre-500 peer-checked:bg-ochre-500/15 rounded-xl p-4 text-center transition">
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
                                            <div class="border-2 border-ink-100 peer-checked:border-red-500 peer-checked:bg-red-50 rounded-xl p-4 text-center transition">
                                                <div class="text-2xl mb-1"><x-app-icon name="x-circle" class="w-6 h-6 inline-block" /></div>
                                                <div class="font-bold text-sm">Indisponible</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <button type="submit"
                                            class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-3 rounded-lg transition">
                                        Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- Vérification d'identité --}}
                    @if($user->isPrestataire())
                        <div id="identity" class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                            <h2 class="text-2xl font-bold text-ink-900 mb-6"><x-app-icon name="shield-check" class="w-6 h-6 inline-block" /> Vérification d'identité</h2>

                            @if($user->identity_verification_status === 'verified')
                                <div class="flex items-center gap-3 bg-forest-600/10 border border-forest-600/30 rounded-lg p-4">
                                    <x-app-icon name="shield-check" class="w-6 h-6 text-forest-700 flex-shrink-0" />
                                    <p class="text-forest-700 font-semibold">Votre identité est vérifiée. Le badge "Vérifié" est visible sur votre profil public.</p>
                                </div>
                            @elseif($user->identity_verification_status === 'pending')
                                <div class="flex items-center gap-3 bg-ochre-500/10 border border-ochre-500/30 rounded-lg p-4">
                                    <x-app-icon name="cog" class="w-6 h-6 text-ochre-600 flex-shrink-0" />
                                    <p class="text-ink-700 font-semibold">Votre document est en cours d'examen par notre équipe.</p>
                                </div>
                            @else
                                @if($user->identity_verification_status === 'rejected')
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                        <p class="text-red-700 font-semibold mb-1">Votre précédent document n'a pas été validé :</p>
                                        <p class="text-red-600 text-sm">{{ $user->identity_rejection_reason }}</p>
                                    </div>
                                @endif
                                <p class="text-ink-500 mb-4 text-sm">
                                    Soumettez une pièce d'identité (CNI, passeport) pour obtenir le badge "Vérifié" sur votre profil. Format JPG, PNG ou PDF, 5 MB maximum.
                                </p>
                                <form method="POST" action="{{ route('identity-verification.store') }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <input type="file" name="identity_document" accept=".jpg,.jpeg,.png,.pdf" required
                                           class="w-full px-4 py-3 border-2 border-dashed border-ink-200 rounded-lg focus:border-terracotta-600 transition text-sm">
                                    <x-input-error :messages="$errors->get('identity_document')" class="mt-2" />
                                    <button type="submit"
                                            class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-3 rounded-lg transition">
                                        Soumettre pour vérification
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    {{-- Mot de passe --}}
                    <div id="password" class="bg-cream-50 rounded-xl p-8 border border-ink-100">
                        <h2 class="text-2xl font-bold text-ink-900 mb-6"><x-app-icon name="lock" class="w-6 h-6 inline-block" /> Modifier le mot de passe</h2>

                        <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="current_password" class="block text-sm font-bold text-ink-700 mb-2">
                                    Mot de passe actuel <span class="text-red-500">*</span>
                                </label>
                                <input type="password"
                                       name="current_password"
                                       id="current_password"
                                       required
                                       class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <x-new-password-fields
                                :password-errors="$errors->updatePassword->get('password')"
                                :confirm-errors="$errors->updatePassword->get('password_confirmation')" />

                            <div class="flex justify-end pt-4">
                                <button type="submit"
                                        class="bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold px-8 py-3 rounded-lg transition">
                                    Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Supprimer le compte --}}
                    <div id="delete" class="bg-red-50 rounded-xl p-8 border-2 border-red-200">
                        <h2 class="text-2xl font-bold text-red-900 mb-4"><x-app-icon name="trash" class="w-6 h-6 inline-block" /> Supprimer le compte</h2>
                        <p class="text-red-700 mb-6">
                            Une fois votre compte supprimé, toutes vos données seront définitivement effacées.
                            Cette action est irréversible.
                        </p>

                        <button type="button"
                                onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-lg transition">
                            Supprimer mon compte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmation suppression --}}
    <div id="delete-modal" class="{{ $errors->userDeletion->isNotEmpty() ? '' : 'hidden' }} fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-cream-50 rounded-xl p-8 max-w-md mx-4">
            <h3 class="text-2xl font-bold text-ink-900 mb-4"><x-app-icon name="exclamation-triangle" class="w-6 h-6 inline-block" /> Confirmer la suppression</h3>
            <p class="text-ink-700 mb-6">
                Êtes-vous sûr de vouloir supprimer votre compte ?
                Cette action est définitive et irréversible.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="mb-6">
                    <label for="delete_password" class="block text-sm font-bold text-ink-700 mb-2">
                        Entrez votre mot de passe pour confirmer
                    </label>
                    <input type="password"
                           name="password"
                           id="delete_password"
                           required
                           class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-red-600 focus:ring-4 focus:ring-red-100 transition">
                    @error('password', 'userDeletion')
                        <p class="text-red-600 text-sm font-semibold mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <button type="button"
                            onclick="document.getElementById('delete-modal').classList.add('hidden')"
                            class="flex-1 bg-ink-100 hover:bg-ink-200 text-ink-700 font-bold px-6 py-3 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-3 rounded-lg transition">
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