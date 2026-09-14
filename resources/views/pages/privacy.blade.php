<x-public-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Hero Section --}}
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white py-16">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-black mb-4">
                    <x-app-icon name="lock" class="w-9 h-9 inline-block align-middle" /> Politique de Confidentialité
                </h1>
                <p class="text-lg text-blue-100">
                    Dernière mise à jour : {{ date('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="container mx-auto px-4 py-12">
            <div class="max-w-4xl mx-auto bg-white rounded-3xl p-8 md:p-12 shadow-lg prose prose-lg max-w-none">
                
                <p class="lead">
                    Chez Azohub, nous prenons très au sérieux la protection de vos données personnelles. 
                    Cette politique explique comment nous collectons, utilisons et protégeons vos informations.
                </p>

                <h2>1. Responsable du traitement</h2>
                <p>
                    Le responsable du traitement de vos données personnelles est :
                </p>
                <ul>
                    <li><strong>Nom :</strong> Azohub</li>
                    <li><strong>Adresse :</strong> Cotonou, Bénin</li>
                    <li><strong>Email :</strong> privacy@azohub.com</li>
                </ul>

                <h2>2. Données collectées</h2>

                <h3>2.1 Données d'inscription</h3>
                <ul>
                    <li>Nom et prénom</li>
                    <li>Adresse email</li>
                    <li>Numéro de téléphone</li>
                    <li>Ville de résidence</li>
                    <li>Mot de passe (crypté)</li>
                </ul>

                <h3>2.2 Données de profil</h3>
                <ul>
                    <li>Photo de profil</li>
                    <li>Biographie</li>
                    <li>Compétences et expertises (prestataires)</li>
                    <li>Zones d'intervention</li>
                    <li>Langues parlées</li>
                </ul>

                <h3>2.3 Données de transaction</h3>
                <ul>
                    <li>Historique des commandes</li>
                    <li>Montants des transactions</li>
                    <li>Informations de paiement (via nos partenaires sécurisés)</li>
                    <li>Historique des retraits (prestataires)</li>
                </ul>

                <h3>2.4 Données de vérification</h3>
                <ul>
                    <li>Documents d'identité (CNI, Passeport)</li>
                    <li>Justificatifs de domicile</li>
                    <li>Ces données sont collectées uniquement pour la vérification d'identité</li>
                </ul>

                <h3>2.5 Données techniques</h3>
                <ul>
                    <li>Adresse IP</li>
                    <li>Type de navigateur</li>
                    <li>Système d'exploitation</li>
                    <li>Pages visitées</li>
                    <li>Durée de visite</li>
                    <li>Cookies (voir section dédiée)</li>
                </ul>

                <h2>3. Base légale et finalités du traitement</h2>

                <h3>3.1 Exécution du contrat</h3>
                <p>
                    Nous traitons vos données pour :
                </p>
                <ul>
                    <li>Créer et gérer votre compte</li>
                    <li>Faciliter les transactions entre clients et prestataires</li>
                    <li>Traiter les paiements et les retraits</li>
                    <li>Fournir le service client</li>
                </ul>

                <h3>3.2 Obligation légale</h3>
                <ul>
                    <li>Vérification d'identité (lutte contre la fraude)</li>
                    <li>Conservation des données fiscales</li>
                    <li>Réponse aux demandes des autorités</li>
                </ul>

                <h3>3.3 Intérêt légitime</h3>
                <ul>
                    <li>Amélioration de nos services</li>
                    <li>Prévention de la fraude</li>
                    <li>Analyse statistique</li>
                    <li>Communication marketing (avec possibilité de désinscription)</li>
                </ul>

                <h3>3.4 Consentement</h3>
                <ul>
                    <li>Newsletter (optionnel)</li>
                    <li>Cookies non essentiels</li>
                    <li>Utilisation de vos contenus à des fins promotionnelles</li>
                </ul>

                <h2>4. Partage des données</h2>

                <h3>4.1 Avec d'autres utilisateurs</h3>
                <ul>
                    <li>Votre profil public est visible par tous les utilisateurs</li>
                    <li>Les clients voient les informations du prestataire lors d'une commande</li>
                    <li>Les prestataires voient les informations du client lors d'une commande</li>
                </ul>

                <h3>4.2 Avec nos partenaires</h3>
                <ul>
                    <li><strong>Prestataires de paiement :</strong> Pour traiter les transactions (FedaPay, etc.)</li>
                    <li><strong>Services d'hébergement :</strong> Pour stocker les données en sécurité</li>
                    <li><strong>Services d'analyse :</strong> Pour améliorer notre plateforme (anonymisé)</li>
                </ul>

                <h3>4.3 Avec les autorités</h3>
                <p>
                    Nous pouvons partager vos données si requis par la loi ou pour protéger nos droits.
                </p>

                <h2>5. Transfert de données</h2>
                <p>
                    Vos données sont principalement stockées au Bénin. Certains de nos partenaires peuvent 
                    être situés dans d'autres pays. Dans ce cas, nous nous assurons qu'ils offrent un niveau 
                    de protection adéquat.
                </p>

                <h2>6. Durée de conservation</h2>
                <ul>
                    <li><strong>Compte actif :</strong> Tant que votre compte existe</li>
                    <li><strong>Après suppression :</strong> 30 jours (puis suppression définitive)</li>
                    <li><strong>Données fiscales :</strong> 10 ans (obligation légale)</li>
                    <li><strong>Données de transaction :</strong> 5 ans</li>
                    <li><strong>Logs techniques :</strong> 12 mois</li>
                </ul>

                <h2>7. Sécurité des données</h2>
                <p>
                    Nous mettons en œuvre des mesures de sécurité appropriées :
                </p>
                <ul>
                    <li>Cryptage SSL pour toutes les communications</li>
                    <li>Mots de passe hashés (bcrypt)</li>
                    <li>Authentification à deux facteurs (optionnelle)</li>
                    <li>Sauvegardes régulières</li>
                    <li>Accès restreint aux données personnelles</li>
                    <li>Surveillance des accès suspects</li>
                </ul>

                <h2>8. Vos droits</h2>

                <h3>8.1 Droit d'accès</h3>
                <p>
                    Vous pouvez demander une copie de toutes vos données personnelles.
                </p>

                <h3>8.2 Droit de rectification</h3>
                <p>
                    Vous pouvez corriger vos données depuis votre profil ou nous contacter.
                </p>

                <h3>8.3 Droit à l'effacement</h3>
                <p>
                    Vous pouvez supprimer votre compte à tout moment depuis les paramètres.
                </p>

                <h3>8.4 Droit d'opposition</h3>
                <p>
                    Vous pouvez vous opposer au traitement de vos données à des fins marketing.
                </p>

                <h3>8.5 Droit à la portabilité</h3>
                <p>
                    Vous pouvez récupérer vos données dans un format structuré et lisible.
                </p>

                <h3>8.6 Droit de limitation</h3>
                <p>
                    Vous pouvez demander la limitation du traitement dans certains cas.
                </p>

                <h3>8.7 Exercice de vos droits</h3>
                <p>
                    Pour exercer vos droits, contactez-nous à : <strong>privacy@azohub.com</strong>
                </p>
                <p>
                    Nous répondrons dans un délai de 30 jours maximum.
                </p>

                <h2>9. Cookies</h2>

                <h3>9.1 Qu'est-ce qu'un cookie ?</h3>
                <p>
                    Un cookie est un petit fichier texte stocké sur votre appareil lors de la visite d'un site web.
                </p>

                <h3>9.2 Types de cookies utilisés</h3>
                <ul>
                    <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement du site (session, authentification)</li>
                    <li><strong>Cookies de performance :</strong> Mesure de l'audience (anonyme)</li>
                    <li><strong>Cookies de fonctionnalité :</strong> Mémorisation de vos préférences</li>
                    <li><strong>Cookies publicitaires :</strong> Personnalisation des publicités (avec consentement)</li>
                </ul>

                <h3>9.3 Gestion des cookies</h3>
                <p>
                    Vous pouvez gérer vos préférences de cookies via :
                </p>
                <ul>
                    <li>Les paramètres de votre navigateur</li>
                    <li>Notre bannière de cookies lors de votre première visite</li>
                </ul>

                <h2>10. Mineurs</h2>
                <p>
                    Notre plateforme est destinée aux personnes de 18 ans et plus. Nous ne collectons pas 
                    sciemment de données d'enfants de moins de 18 ans.
                </p>

                <h2>11. Modifications de la politique</h2>
                <p>
                    Nous pouvons modifier cette politique à tout moment. En cas de changement important, 
                    nous vous informerons par email et/ou via une notification sur la plateforme.
                </p>

                <h2>12. Contact</h2>
                <p>
                    Pour toute question concernant cette politique de confidentialité :
                </p>
                <ul>
                    <li><strong>Email :</strong> privacy@azohub.com</li>
                    <li><strong>Formulaire de contact :</strong> <a href="{{ route('contact') }}" class="text-blue-600 hover:text-blue-800 font-bold">Nous contacter</a></li>
                </ul>

                <h2>13. Réclamation</h2>
                <p>
                    Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une 
                    réclamation auprès de l'autorité de protection des données compétente au Bénin.
                </p>

                <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded mt-8">
                    <p class="font-bold text-green-900 mb-2"><x-app-icon name="check-circle" class="w-5 h-5 inline-block" /> Notre engagement</p>
                    <p class="text-green-800">
                        Nous nous engageons à protéger vos données personnelles et à respecter votre vie privée. 
                        La confiance que vous nous accordez est notre priorité.
                    </p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded mt-4">
                    <p class="font-bold text-blue-900 mb-2"><x-app-icon name="book" class="w-5 h-5 inline-block" /> Documents connexes</p>
                    <ul class="mb-0">
                        <li><a href="{{ route('terms') }}" class="text-blue-600 hover:text-blue-800 font-bold">Conditions Générales d'Utilisation</a></li>
                        <li><a href="{{ route('faq') }}" class="text-blue-600 hover:text-blue-800 font-bold">Foire Aux Questions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>