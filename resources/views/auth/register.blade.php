<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <span class="text-5xl font-black text-white">Azo</span>
                    <span class="text-5xl font-black text-yellow-400">hub</span>
                    <span class="ml-2 inline-flex items-center justify-center w-8 h-5 rounded-sm overflow-hidden align-middle">
                        <svg viewBox="0 0 45 30" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <rect width="45" height="30" fill="#E8112D"/>
                            <rect width="45" height="15" fill="#FCD116"/>
                            <rect width="18" height="30" fill="#008751"/>
                        </svg>
                    </span>
                </a>
                <h2 class="text-2xl font-bold text-white mb-2">Créez votre compte</h2>
                <p class="text-blue-200">Rejoignez la première plateforme de services au Bénin</p>
            </div>

            {{-- Formulaire --}}
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    {{-- Choix du rôle --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3">
                            Je souhaite
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="role" 
                                       value="client" 
                                       checked
                                       class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-blue-900 peer-checked:bg-blue-50 rounded-2xl p-6 text-center transition hover:border-blue-300">
                                    <div class="mb-2"><x-app-icon name="user" class="w-10 h-10 inline-block" /></div>
                                    <div class="font-bold text-gray-900">Trouver un service</div>
                                    <div class="text-sm text-gray-600 mt-1">Je suis client</div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="role" 
                                       value="prestataire"
                                       class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-yellow-400 peer-checked:bg-yellow-50 rounded-2xl p-6 text-center transition hover:border-yellow-300">
                                    <div class="mb-2"><x-app-icon name="briefcase" class="w-10 h-10 inline-block" /></div>
                                    <div class="font-bold text-gray-900">Proposer mes services</div>
                                    <div class="text-sm text-gray-600 mt-1">Je suis prestataire</div>
                                </div>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                                Nom complet <span class="text-red-500">*</span>
                            </label>
                            <input id="name" 
                                   type="text" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="name"
                                   placeholder="Ex: Jean Dupont"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                                Adresse e-mail <span class="text-red-500">*</span>
                            </label>
                            <input id="email" 
                                   type="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autocomplete="username"
                                   placeholder="exemple@email.com"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                                Téléphone <span class="text-red-500">*</span>
                            </label>
                            <input id="phone" 
                                   type="tel" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   required
                                   placeholder="+229 XX XX XX XX"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-bold text-gray-700 mb-2">
                                Ville <span class="text-red-500">*</span>
                            </label>
                            <select id="city" 
                                    name="city" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                                <option value="">Sélectionnez votre ville</option>
                                @foreach(config('communes') as $departement => $villes)
                                    <optgroup label="{{ $departement }}">
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville }}" {{ old('city') === $ville ? 'selected' : '' }}>
                                                {{ $ville }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                                Mot de passe <span class="text-red-500">*</span>
                            </label>
                            <input id="password" 
                                   type="password" 
                                   name="password" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Min. 8 caractères"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">
                                Confirmer le mot de passe <span class="text-red-500">*</span>
                            </label>
                            <input id="password_confirmation" 
                                   type="password" 
                                   name="password_confirmation" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Retapez le mot de passe"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox" 
                               name="terms" 
                               required
                               class="mt-1 rounded border-gray-300 text-blue-900 focus:ring-blue-900 focus:ring-2">
                        <label class="ml-2 text-sm text-gray-700">
                            J'accepte les <a href="#" class="font-bold text-blue-900 hover:text-blue-700">conditions d'utilisation</a> 
                            et la <a href="#" class="font-bold text-blue-900 hover:text-blue-700">politique de confidentialité</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-blue-900 font-black py-4 rounded-2xl transition transform hover:scale-105 shadow-lg text-lg">
                        Créer mon compte gratuitement
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t">
                        <p class="text-sm text-gray-600">
                            Vous avez déjà un compte ?
                            <a href="{{ route('login') }}" class="font-bold text-blue-900 hover:text-blue-700 transition">
                                Connectez-vous
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            {{-- Retour accueil --}}
            <div class="text-center mt-6">
                <a href="{{ route('home') }}" class="text-white hover:text-yellow-400 font-bold transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>