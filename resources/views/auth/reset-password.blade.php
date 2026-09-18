<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Nouveau mot de passe</h2>
                <p class="text-cream-100/60">Choisissez un mot de passe pour votre compte</p>
            </div>

            {{-- Carte --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <x-input-label for="email" :value="__('Adresse email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Mot de passe')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                      type="password"
                                      name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Réinitialiser le mot de passe') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
