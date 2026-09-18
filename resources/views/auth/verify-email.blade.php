<x-guest-layout>
    <div class="mb-4 text-sm text-ink-500">
        {{ __('Merci de votre inscription ! Avant de commencer, pouvez-vous confirmer votre adresse email en cliquant sur le lien que nous venons de vous envoyer ? Si vous ne l\'avez pas reçu, nous pouvons vous en envoyer un autre avec plaisir.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-forest-700">
            {{ __('Un nouveau lien de confirmation a été envoyé à l\'adresse email indiquée lors de votre inscription.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Renvoyer l\'email de confirmation') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-ink-500 hover:text-ink-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta-600">
                {{ __('Déconnexion') }}
            </button>
        </form>
    </div>
</x-guest-layout>
