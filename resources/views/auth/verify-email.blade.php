<x-guest-layout>
    <div class="min-h-screen bg-ink-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-4">
                    <x-logo-lockup class="h-12 w-auto" :dark="true" />
                </a>
                <h2 class="text-2xl font-bold text-cream-50 mb-2">Confirmez votre email</h2>
                <p class="text-cream-100/60">Encore une étape avant de commencer</p>
            </div>

            {{-- Carte --}}
            <div class="bg-cream-50 rounded-xl border border-ink-100 p-8">
                <div class="mb-6 text-sm text-ink-500">
                    {{ __('Merci de votre inscription ! Avant de commencer, pouvez-vous confirmer votre adresse email en cliquant sur le lien que nous venons de vous envoyer ? Si vous ne l\'avez pas reçu, nous pouvons vous en envoyer un autre avec plaisir.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 font-medium text-sm text-forest-700 bg-forest-600/10 border border-forest-600/20 rounded-lg px-4 py-3">
                        {{ __('Un nouveau lien de confirmation a été envoyé à l\'adresse email indiquée lors de votre inscription.') }}
                    </div>
                @endif

                <div class="flex items-center justify-between gap-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-primary-button>
                            {{ __('Renvoyer l\'email de confirmation') }}
                        </x-primary-button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-ink-500 hover:text-ink-900 font-semibold rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta-600">
                            {{ __('Déconnexion') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
