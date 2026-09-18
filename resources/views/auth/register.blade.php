<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Créez votre compte</h2>
                <p class="text-cream-100/60">Rejoignez la première plateforme de services au Bénin</p>
            </div>

            {{-- Formulaire --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    {{-- Choix du rôle --}}
                    <div>
                        <label class="block text-sm font-bold text-ink-700 mb-3">
                            Je souhaite
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative cursor-pointer">
                                <input type="radio"
                                       name="role"
                                       value="client"
                                       checked
                                       class="peer sr-only">
                                <div class="border-2 border-ink-100 peer-checked:border-terracotta-600 peer-checked:bg-terracotta-50 rounded-lg p-6 text-center transition hover:border-terracotta-600/40">
                                    <div class="mb-2"><x-app-icon name="user" class="w-10 h-10 inline-block" /></div>
                                    <div class="font-bold text-ink-900">Trouver un service</div>
                                    <div class="text-sm text-ink-500 mt-1">Je suis client</div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio"
                                       name="role"
                                       value="prestataire"
                                       class="peer sr-only">
                                <div class="border-2 border-ink-100 peer-checked:border-ochre-500 peer-checked:bg-ochre-500/15 rounded-lg p-6 text-center transition hover:border-ochre-500/50">
                                    <div class="mb-2"><x-app-icon name="briefcase" class="w-10 h-10 inline-block" /></div>
                                    <div class="font-bold text-ink-900">Proposer mes services</div>
                                    <div class="text-sm text-ink-500 mt-1">Je suis prestataire</div>
                                </div>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-ink-700 mb-2">
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
                                   class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-ink-700 mb-2">
                                Adresse e-mail <span class="text-red-500">*</span>
                            </label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="username"
                                   placeholder="exemple@email.com"
                                   class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-bold text-ink-700 mb-2">
                                Téléphone <span class="text-red-500">*</span>
                            </label>
                            <input id="phone"
                                   type="tel"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   required
                                   placeholder="+229 XX XX XX XX"
                                   class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-bold text-ink-700 mb-2">
                                Ville <span class="text-red-500">*</span>
                            </label>
                            <select id="city"
                                    name="city"
                                    required
                                    class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
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
                            <label for="password" class="block text-sm font-bold text-ink-700 mb-2">
                                Mot de passe <span class="text-red-500">*</span>
                            </label>
                            <x-password-input id="password"
                                   name="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="Min. 8 caractères" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-ink-700 mb-2">
                                Confirmer le mot de passe <span class="text-red-500">*</span>
                            </label>
                            <x-password-input id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   placeholder="Retapez le mot de passe" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox"
                               name="terms"
                               required
                               class="mt-1 rounded border-ink-200 text-terracotta-600 focus:ring-terracotta-600 focus:ring-2">
                        <label class="ml-2 text-sm text-ink-700">
                            J'accepte les <a href="{{ route('terms') }}" target="_blank" class="font-bold text-terracotta-600 hover:text-terracotta-700">conditions d'utilisation</a>
                            et la <a href="{{ route('privacy') }}" target="_blank" class="font-bold text-terracotta-600 hover:text-terracotta-700">politique de confidentialité</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold py-4 rounded-lg transition shadow-lg text-lg">
                        Créer mon compte gratuitement
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t border-ink-100">
                        <p class="text-sm text-ink-500">
                            Vous avez déjà un compte ?
                            <a href="{{ route('login') }}" class="font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                                Connectez-vous
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            {{-- Retour accueil --}}
            <div class="text-center mt-6">
                <a href="{{ route('home') }}" class="text-cream-50 hover:text-ochre-500 font-bold transition">
                    ← Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>