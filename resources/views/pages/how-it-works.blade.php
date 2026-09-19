<x-app-layout>
    <div class="min-h-screen bg-cream">
        {{-- Hero Section --}}
        <div class="bg-ink-900 text-cream-50 py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-serif font-medium mb-6">
                    Comment ça marche ?
                </h1>
                <p class="text-xl md:text-2xl text-cream-50/80 max-w-3xl mx-auto">
                    Découvrez comment Azohub connecte les clients et les prestataires en 3 étapes simples
                </p>
            </div>
        </div>

        {{-- Pour les Clients --}}
        <div class="py-20 bg-cream-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-serif font-medium text-ink-900 mb-4">
                        <x-app-icon name="user" class="w-8 h-8 inline-block align-middle" /> Pour les Clients
                    </h2>
                    <p class="text-xl text-ink-500">
                        Trouvez le prestataire parfait en quelques clics
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-12 max-w-6xl mx-auto">
                    {{-- Étape 1 --}}
                    <div class="text-center">
                        <div class="bg-terracotta-50 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-app-icon name="search" class="w-14 h-14 text-terracotta-600" />
                        </div>
                        <div class="bg-ink-900 text-cream-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                            1
                        </div>
                        <h3 class="text-2xl font-bold text-ink-900 mb-4">Recherchez</h3>
                        <p class="text-ink-500">
                            Parcourez les services par catégorie ou recherchez directement ce dont vous avez besoin.
                        </p>
                    </div>

                    {{-- Étape 2 --}}
                    <div class="text-center">
                        <div class="bg-ochre-500/15 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-app-icon name="card" class="w-14 h-14 text-ochre-600" />
                        </div>
                        <div class="bg-ink-900 text-cream-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                            2
                        </div>
                        <h3 class="text-2xl font-bold text-ink-900 mb-4">Commandez</h3>
                        <p class="text-ink-500">
                            Choisissez votre service, payez en toute sécurité. Votre argent est protégé jusqu'à la livraison.
                        </p>
                    </div>

                    {{-- Étape 3 --}}
                    <div class="text-center">
                        <div class="bg-forest-600/10 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-app-icon name="check-circle" class="w-14 h-14 text-forest-700" />
                        </div>
                        <div class="bg-ink-900 text-cream-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                            3
                        </div>
                        <h3 class="text-2xl font-bold text-ink-900 mb-4">Recevez & Validez</h3>
                        <p class="text-ink-500">
                            Le prestataire livre le travail. Vérifiez et validez. C'est aussi simple que ça !
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pour les Prestataires --}}
        <div class="py-20 bg-cream">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-serif font-medium text-ink-900 mb-4">
                        <x-app-icon name="briefcase" class="w-8 h-8 inline-block align-middle" /> Pour les Prestataires
                    </h2>
                    <p class="text-xl text-ink-500">
                        Transformez vos compétences en revenus
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-12 max-w-6xl mx-auto">
                    {{-- Étape 1 --}}
                    <div class="text-center">
                        <div class="bg-purple-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-app-icon name="pencil-square" class="w-14 h-14 text-purple-600" />
                        </div>
                        <div class="bg-terracotta-600 text-cream-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                            1
                        </div>
                        <h3 class="text-2xl font-bold text-ink-900 mb-4">Créez vos services</h3>
                        <p class="text-ink-500">
                            Inscrivez-vous, complétez votre profil et créez vos services avec prix et délais.
                        </p>
                    </div>

                    {{-- Étape 2 --}}
                    <div class="text-center">
                        <div class="bg-terracotta-50 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-app-icon name="rocket" class="w-14 h-14 text-terracotta-600" />
                        </div>
                        <div class="bg-terracotta-600 text-cream-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                            2
                        </div>
                        <h3 class="text-2xl font-bold text-ink-900 mb-4">Recevez des commandes</h3>
                        <p class="text-ink-500">
                            Les clients vous trouvent, commandent vos services. Vous êtes notifié instantanément.
                        </p>
                    </div>

                    {{-- Étape 3 --}}
                    <div class="text-center">
                        <div class="bg-forest-600/10 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-app-icon name="banknotes" class="w-14 h-14 text-forest-700" />
                        </div>
                        <div class="bg-terracotta-600 text-cream-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                            3
                        </div>
                        <h3 class="text-2xl font-bold text-ink-900 mb-4">Livrez & Gagnez</h3>
                        <p class="text-ink-500">
                            Livrez le travail, le client valide, et vous recevez votre paiement. Simple et sécurisé !
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Garanties --}}
        <div class="py-20 bg-cream-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-serif font-medium text-ink-900 mb-4">
                        <x-app-icon name="shield-check" class="w-8 h-8 inline-block align-middle" /> Nos Garanties
                    </h2>
                    <p class="text-xl text-ink-500">
                        Votre sécurité est notre priorité
                    </p>
                </div>

                <div class="grid md:grid-cols-4 gap-8 max-w-6xl mx-auto">
                    <div class="bg-terracotta-50 rounded-xl p-6 text-center">
                        <div class="mb-4"><x-app-icon name="lock" class="w-10 h-10 inline-block text-terracotta-600" /></div>
                        <h3 class="font-bold text-ink-900 mb-2">Paiement sécurisé</h3>
                        <p class="text-sm text-ink-500">Vos transactions sont protégées par cryptage SSL</p>
                    </div>

                    <div class="bg-forest-600/10 rounded-xl p-6 text-center">
                        <div class="mb-4"><x-app-icon name="card" class="w-10 h-10 inline-block text-forest-700" /></div>
                        <h3 class="font-bold text-ink-900 mb-2">Argent protégé</h3>
                        <p class="text-sm text-ink-500">Votre paiement est gardé jusqu'à la validation</p>
                    </div>

                    <div class="bg-ochre-500/15 rounded-xl p-6 text-center">
                        <div class="mb-4"><x-app-icon name="handshake" class="w-10 h-10 inline-block text-ochre-600" /></div>
                        <h3 class="font-bold text-ink-900 mb-2">Support 24/7</h3>
                        <p class="text-sm text-ink-500">Notre équipe est là pour vous aider</p>
                    </div>

                    <div class="bg-purple-50 rounded-xl p-6 text-center">
                        <div class="mb-4"><x-app-icon name="star" class="w-10 h-10 inline-block text-purple-600" /></div>
                        <h3 class="font-bold text-ink-900 mb-2">Avis vérifiés</h3>
                        <p class="text-sm text-ink-500">Tous les avis proviennent de vraies commandes</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA Final --}}
        <div class="py-20 bg-ink-900 text-cream-50">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-4xl font-serif font-medium mb-6">
                    Prêt à commencer ?
                </h2>
                <p class="text-xl text-cream-50/80 mb-8 max-w-2xl mx-auto">
                    Rejoignez des milliers d'utilisateurs qui font confiance à Azohub
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                        <a href="{{ route('register') }}?role=client"
                           class="bg-cream-50 text-ink-900 font-bold px-8 py-4 rounded-lg hover:bg-cream transition shadow-md">
<x-app-icon name="user" class="w-5 h-5 inline-block" /> Je cherche un service
                        </a>
                        <a href="{{ route('register') }}?role=prestataire"
                           class="bg-terracotta-600 text-cream-50 font-bold px-8 py-4 rounded-lg hover:bg-terracotta-700 transition shadow-md">
<x-app-icon name="briefcase" class="w-5 h-5 inline-block" /> Je propose mes services
                        </a>
                    @else
                        <a href="{{ route('services.index') }}"
                           class="bg-cream-50 text-ink-900 font-bold px-8 py-4 rounded-lg hover:bg-cream transition shadow-md">
<x-app-icon name="search" class="w-5 h-5 inline-block" /> Parcourir les services
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</x-app-layout>