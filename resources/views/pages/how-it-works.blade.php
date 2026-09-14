<x-public-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Hero Section --}}
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-5xl md:text-6xl font-black mb-6">
                    Comment ça marche ?
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">
                    Découvrez comment Azohub connecte les clients et les prestataires en 3 étapes simples
                </p>
            </div>
        </div>

        {{-- Pour les Clients --}}
        <div class="py-20 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-black text-gray-900 mb-4">
                        👤 Pour les Clients
                    </h2>
                    <p class="text-xl text-gray-600">
                        Trouvez le prestataire parfait en quelques clics
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-12 max-w-6xl mx-auto">
                    {{-- Étape 1 --}}
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-200 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl transform hover:scale-110 transition">
                            🔍
                        </div>
                        <div class="bg-blue-900 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                            1
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Recherchez</h3>
                        <p class="text-gray-600">
                            Parcourez les services par catégorie ou recherchez directement ce dont vous avez besoin.
                        </p>
                    </div>

                    {{-- Étape 2 --}}
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl transform hover:scale-110 transition">
                            💳
                        </div>
                        <div class="bg-blue-900 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                            2
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Commandez</h3>
                        <p class="text-gray-600">
                            Choisissez votre service, payez en toute sécurité. Votre argent est protégé jusqu'à la livraison.
                        </p>
                    </div>

                    {{-- Étape 3 --}}
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-green-100 to-green-200 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl transform hover:scale-110 transition">
                            ✅
                        </div>
                        <div class="bg-blue-900 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                            3
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Recevez & Validez</h3>
                        <p class="text-gray-600">
                            Le prestataire livre le travail. Vérifiez et validez. C'est aussi simple que ça !
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pour les Prestataires --}}
        <div class="py-20 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-black text-gray-900 mb-4">
                        💼 Pour les Prestataires
                    </h2>
                    <p class="text-xl text-gray-600">
                        Transformez vos compétences en revenus
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-12 max-w-6xl mx-auto">
                    {{-- Étape 1 --}}
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-purple-100 to-purple-200 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl transform hover:scale-110 transition">
                            📝
                        </div>
                        <div class="bg-yellow-400 text-blue-900 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                            1
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Créez vos services</h3>
                        <p class="text-gray-600">
                            Inscrivez-vous, complétez votre profil et créez vos services avec prix et délais.
                        </p>
                    </div>

                    {{-- Étape 2 --}}
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-200 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl transform hover:scale-110 transition">
                            🚀
                        </div>
                        <div class="bg-yellow-400 text-blue-900 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                            2
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Recevez des commandes</h3>
                        <p class="text-gray-600">
                            Les clients vous trouvent, commandent vos services. Vous êtes notifié instantanément.
                        </p>
                    </div>

                    {{-- Étape 3 --}}
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-green-100 to-green-200 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 text-6xl transform hover:scale-110 transition">
                            💰
                        </div>
                        <div class="bg-yellow-400 text-blue-900 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                            3
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Livrez & Gagnez</h3>
                        <p class="text-gray-600">
                            Livrez le travail, le client valide, et vous recevez votre paiement. Simple et sécurisé !
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Garanties --}}
        <div class="py-20 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-black text-gray-900 mb-4">
                        🛡️ Nos Garanties
                    </h2>
                    <p class="text-xl text-gray-600">
                        Votre sécurité est notre priorité
                    </p>
                </div>

                <div class="grid md:grid-cols-4 gap-8 max-w-6xl mx-auto">
                    <div class="bg-blue-50 rounded-3xl p-6 text-center transform hover:scale-105 transition">
                        <div class="text-5xl mb-4">🔒</div>
                        <h3 class="font-bold text-gray-900 mb-2">Paiement sécurisé</h3>
                        <p class="text-sm text-gray-600">Vos transactions sont protégées par cryptage SSL</p>
                    </div>

                    <div class="bg-green-50 rounded-3xl p-6 text-center transform hover:scale-105 transition">
                        <div class="text-5xl mb-4">💳</div>
                        <h3 class="font-bold text-gray-900 mb-2">Argent protégé</h3>
                        <p class="text-sm text-gray-600">Votre paiement est gardé jusqu'à la validation</p>
                    </div>

                    <div class="bg-yellow-50 rounded-3xl p-6 text-center transform hover:scale-105 transition">
                        <div class="text-5xl mb-4">🤝</div>
                        <h3 class="font-bold text-gray-900 mb-2">Support 24/7</h3>
                        <p class="text-sm text-gray-600">Notre équipe est là pour vous aider</p>
                    </div>

                    <div class="bg-purple-50 rounded-3xl p-6 text-center transform hover:scale-105 transition">
                        <div class="text-5xl mb-4">⭐</div>
                        <h3 class="font-bold text-gray-900 mb-2">Avis vérifiés</h3>
                        <p class="text-sm text-gray-600">Tous les avis proviennent de vraies commandes</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA Final --}}
        <div class="py-20 bg-gradient-to-r from-blue-900 to-blue-800 text-white">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-4xl font-black mb-6">
                    Prêt à commencer ?
                </h2>
                <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                    Rejoignez des milliers d'utilisateurs qui font confiance à Azohub
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                        <a href="{{ route('register') }}?role=client" 
                           class="bg-white text-blue-900 font-black px-8 py-4 rounded-full hover:bg-blue-50 transition transform hover:scale-105 shadow-xl">
                            👤 Je cherche un service
                        </a>
                        <a href="{{ route('register') }}?role=prestataire" 
                           class="bg-yellow-400 text-blue-900 font-black px-8 py-4 rounded-full hover:bg-yellow-300 transition transform hover:scale-105 shadow-xl">
                            💼 Je propose mes services
                        </a>
                    @else
                        <a href="{{ route('services.index') }}" 
                           class="bg-white text-blue-900 font-black px-8 py-4 rounded-full hover:bg-blue-50 transition transform hover:scale-105 shadow-xl">
                            🔍 Parcourir les services
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</x-public-layout>