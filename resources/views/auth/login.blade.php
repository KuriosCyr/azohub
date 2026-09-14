<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <span class="text-5xl font-black text-white">Azo</span>
                    <span class="text-5xl font-black text-yellow-400">hub</span>
                    <span class="ml-2 text-3xl">🇧🇯</span>
                </a>
                <h2 class="text-2xl font-bold text-white mb-2">Bon retour !</h2>
                <p class="text-blue-200">Connectez-vous pour accéder à votre compte</p>
            </div>

            {{-- Formulaire --}}
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                            Adresse e-mail
                        </label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                            Mot de passe
                        </label>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-900 focus:ring-4 focus:ring-blue-100 transition">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="remember" 
                                   class="rounded border-gray-300 text-blue-900 focus:ring-blue-900 focus:ring-2">
                            <span class="ml-2 text-sm text-gray-700">Se souvenir de moi</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-bold text-blue-900 hover:text-blue-700 transition">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-900 to-blue-800 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-4 rounded-2xl transition transform hover:scale-105 shadow-lg">
                        Se connecter
                    </button>

                    <!-- Register Link -->
                    <div class="text-center pt-4 border-t">
                        <p class="text-sm text-gray-600">
                            Pas encore de compte ?
                            <a href="{{ route('register') }}" class="font-bold text-blue-900 hover:text-blue-700 transition">
                                Inscrivez-vous gratuitement
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