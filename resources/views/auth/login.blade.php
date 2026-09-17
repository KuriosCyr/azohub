<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Bon retour !</h2>
                <p class="text-cream-100/60">Connectez-vous pour accéder à votre compte</p>
            </div>

            {{-- Formulaire --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-ink-700 mb-2">
                            Adresse e-mail
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-bold text-ink-700 mb-2">
                            Mot de passe
                        </label>
                        <x-password-input id="password"
                               name="password"
                               required
                               autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="remember"
                                   class="rounded border-ink-200 text-terracotta-600 focus:ring-terracotta-600 focus:ring-2">
                            <span class="ml-2 text-sm text-ink-700">Se souvenir de moi</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-4 rounded-lg transition shadow-lg">
                        Se connecter
                    </button>

                    <!-- Register Link -->
                    <div class="text-center pt-4 border-t border-ink-100">
                        <p class="text-sm text-ink-500">
                            Pas encore de compte ?
                            <a href="{{ route('register') }}" class="font-bold text-terracotta-600 hover:text-terracotta-700 transition">
                                Inscrivez-vous gratuitement
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