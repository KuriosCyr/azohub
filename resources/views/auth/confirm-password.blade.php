<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Confirmez votre mot de passe</h2>
                <p class="text-cream-100/60">Zone sécurisée — on veut être sûr que c'est bien vous</p>
            </div>

            {{-- Carte --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                <div class="mb-6 text-sm text-ink-500">
                    {{ __('Ceci est une zone sécurisée de l\'application. Veuillez confirmer votre mot de passe avant de continuer.') }}
                </div>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="password" :value="__('Mot de passe')" />
                        <x-text-input id="password" class="block mt-1 w-full"
                                      type="password"
                                      name="password"
                                      required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Confirmer') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
