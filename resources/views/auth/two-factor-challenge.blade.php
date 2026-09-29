<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Vérification en deux étapes</h2>
                <p class="text-cream-100/60">Entrez le code à 6 chiffres envoyé par email</p>
            </div>

            {{-- Formulaire --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-forest-600/10 border border-forest-600/30 p-3 text-sm text-forest-700 font-semibold">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-bold text-ink-700 mb-2">
                            Code de connexion
                        </label>
                        <input id="code"
                               type="text"
                               name="code"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               maxlength="6"
                               required
                               autofocus
                               placeholder="123456"
                               class="w-full px-4 py-3 border-2 border-ink-100 rounded-lg focus:border-terracotta-600 focus:ring-4 focus:ring-terracotta-50 transition text-center text-2xl font-bold tracking-[0.4em]">
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <button type="submit"
                            class="w-full bg-ink-900 hover:bg-ink-700 text-cream-50 font-bold py-4 rounded-lg transition shadow-lg">
                        Vérifier
                    </button>
                </form>

                <form method="POST" action="{{ route('two-factor.resend') }}" class="text-center pt-4 mt-4 border-t border-ink-100">
                    @csrf
                    <p class="text-sm text-ink-500 mb-2">Vous n'avez pas reçu le code ?</p>
                    <button type="submit" class="font-bold text-terracotta-600 hover:text-terracotta-700 transition text-sm">
                        Renvoyer un code
                    </button>
                </form>
            </div>

            {{-- Retour connexion --}}
            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-cream-50 hover:text-ochre-500 font-bold transition">
                    ← Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
