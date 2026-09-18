<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Mot de passe oublié</h2>
                <p class="text-cream-100/60">Pas de souci, on vous envoie un lien</p>
            </div>

            {{-- Carte --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                <div class="mb-6 text-sm text-ink-500">
                    {{ __('Indiquez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.') }}
                </div>

                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Adresse email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Envoyer le lien de réinitialisation') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
