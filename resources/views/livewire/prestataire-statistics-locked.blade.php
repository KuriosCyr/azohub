<div class="py-8 bg-cream min-h-screen">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-cream-50 rounded-xl border border-ink-100 p-10 text-center">
            <div class="w-16 h-16 rounded-full bg-ochre-500/15 flex items-center justify-center mx-auto mb-6">
                <x-app-icon name="lock" class="w-8 h-8 text-ochre-600" />
            </div>
            <h1 class="text-2xl font-serif font-medium text-ink-900 mb-3">Statistiques avancées</h1>
            <p class="text-ink-500 mb-8">
                Vues de profil, évolution de vos revenus, taux de conversion... Cette page est réservée
                aux prestataires abonnés au plan Pro ou Premium.
            </p>
            <a href="{{ route('prestataire.subscription') }}"
               class="inline-block bg-terracotta-600 hover:bg-terracotta-700 text-cream-50 font-bold px-8 py-3 rounded-lg transition shadow-md">
                Voir les plans d'abonnement
            </a>
        </div>
    </div>
</div>
