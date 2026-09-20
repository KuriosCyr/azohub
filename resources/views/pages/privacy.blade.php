<x-legal-page
    title="Politique de Confidentialité"
    subtitle="Quelles données nous collectons, pourquoi, avec qui nous les partageons et comment vous gardez la main dessus."
    updated="20/09/2026"
    other-label="Conditions d'utilisation"
    :other-route="route('terms')">

    <p class="text-lg leading-relaxed text-ink-700">
        Chez Azohub, nous prenons très au sérieux la protection de vos données personnelles.
        Cette politique explique comment nous collectons, utilisons et protégeons vos informations.
    </p>

    {{-- L'essentiel --}}
    <div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-ink-400">L'essentiel en un coup d'œil</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="user" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">Suppression à tout moment</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">Vos données identifiantes sont anonymisées immédiatement à la suppression du compte.</p>
            </div>
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="envelope" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">Réponse sous 30 jours</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">à toute demande d'accès, de rectification ou d'effacement de vos données.</p>
            </div>
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="lock" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">Mots de passe protégés</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">jamais stockés en clair : ils sont hashés (bcrypt).</p>
            </div>
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="check-circle" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">Pièces d'identité</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">collectées uniquement pour vérifier votre identité, rien d'autre.</p>
            </div>
        </div>
    </div>

    <x-legal-section number="1" title="Responsable du traitement">
        <p>Le responsable du traitement de vos données personnelles est :</p>
        <ul>
            <li><strong>Nom :</strong> Azohub</li>
            <li><strong>Adresse :</strong> Cotonou, Bénin</li>
            <li><strong>Email :</strong> privacy@azohub.bj</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="2" title="Données collectées">
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
    </x-legal-section>

    <x-legal-section number="3" title="Base légale et finalités du traitement">
        <h3>3.1 Exécution du contrat</h3>
        <p>Nous traitons vos données pour :</p>
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
    </x-legal-section>

    <x-legal-section number="4" title="Partage des données">
        <h3>4.1 Avec d'autres utilisateurs</h3>
        <ul>
            <li>Votre profil public est visible par tous les utilisateurs</li>
            <li>Les clients voient les informations du prestataire lors d'une commande</li>
            <li>Les prestataires voient les informations du client lors d'une commande</li>
        </ul>

        <h3>4.2 Avec nos partenaires</h3>
        <ul>
            <li><strong>Prestataires de paiement :</strong> pour traiter les transactions (FedaPay, etc.)</li>
            <li><strong>Services d'hébergement :</strong> pour stocker les données en sécurité</li>
            <li><strong>Services d'analyse :</strong> pour améliorer notre plateforme (anonymisé)</li>
        </ul>

        <h3>4.3 Avec les autorités</h3>
        <p>Nous pouvons partager vos données si requis par la loi ou pour protéger nos droits.</p>
    </x-legal-section>

    <x-legal-section number="5" title="Transfert de données">
        <p>
            Vos données sont principalement stockées au Bénin. Certains de nos partenaires peuvent
            être situés dans d'autres pays. Dans ce cas, nous nous assurons qu'ils offrent un niveau
            de protection adéquat.
        </p>
    </x-legal-section>

    <x-legal-section number="6" title="Durée de conservation">
        <ul>
            <li><strong>Compte actif :</strong> tant que votre compte existe</li>
            <li><strong>Après suppression :</strong> vos données identifiantes (nom, email, téléphone, photo, pièce d'identité) sont anonymisées immédiatement et de façon irréversible ; certaines données non identifiantes liées à vos commandes passées sont conservées pour préserver l'historique des autres utilisateurs concernés</li>
            <li><strong>Données fiscales :</strong> 10 ans (obligation légale)</li>
            <li><strong>Données de transaction :</strong> 5 ans</li>
            <li><strong>Logs techniques :</strong> 12 mois</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="7" title="Sécurité des données">
        <p>Nous mettons en œuvre des mesures de sécurité appropriées :</p>
        <ul>
            <li>Cryptage SSL pour toutes les communications</li>
            <li>Mots de passe hashés (bcrypt)</li>
            <li>Authentification à deux facteurs (optionnelle)</li>
            <li>Sauvegardes régulières</li>
            <li>Accès restreint aux données personnelles</li>
            <li>Surveillance des accès suspects</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="8" title="Vos droits">
        <h3>8.1 Droit d'accès</h3>
        <p>Vous pouvez demander une copie de toutes vos données personnelles.</p>

        <h3>8.2 Droit de rectification</h3>
        <p>Vous pouvez corriger vos données depuis votre profil ou nous contacter.</p>

        <h3>8.3 Droit à l'effacement</h3>
        <p>Vous pouvez supprimer votre compte à tout moment depuis les paramètres.</p>

        <h3>8.4 Droit d'opposition</h3>
        <p>Vous pouvez vous opposer au traitement de vos données à des fins marketing.</p>

        <h3>8.5 Droit à la portabilité</h3>
        <p>Vous pouvez récupérer vos données dans un format structuré et lisible.</p>

        <h3>8.6 Droit de limitation</h3>
        <p>Vous pouvez demander la limitation du traitement dans certains cas.</p>

        <h3>8.7 Exercice de vos droits</h3>
        <p>Pour exercer vos droits, contactez-nous à : <strong>privacy@azohub.bj</strong></p>
        <p>Nous répondrons dans un délai de 30 jours maximum.</p>
    </x-legal-section>

    <x-legal-section number="9" title="Cookies">
        <h3>9.1 Qu'est-ce qu'un cookie ?</h3>
        <p>Un cookie est un petit fichier texte stocké sur votre appareil lors de la visite d'un site web.</p>

        <h3>9.2 Types de cookies utilisés</h3>
        <ul>
            <li><strong>Cookies essentiels :</strong> nécessaires au fonctionnement du site (session, authentification)</li>
            <li><strong>Cookies de performance :</strong> mesure de l'audience (anonyme)</li>
            <li><strong>Cookies de fonctionnalité :</strong> mémorisation de vos préférences</li>
            <li><strong>Cookies publicitaires :</strong> personnalisation des publicités (avec consentement)</li>
        </ul>

        <h3>9.3 Gestion des cookies</h3>
        <p>Vous pouvez gérer vos préférences de cookies via :</p>
        <ul>
            <li>Les paramètres de votre navigateur</li>
            <li>Notre bannière de cookies lors de votre première visite</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="10" title="Mineurs">
        <p>
            Notre plateforme est destinée aux personnes de 18 ans et plus. Nous ne collectons pas
            sciemment de données d'enfants de moins de 18 ans.
        </p>
    </x-legal-section>

    <x-legal-section number="11" title="Modifications de la politique">
        <p>
            Nous pouvons modifier cette politique à tout moment. En cas de changement important,
            nous vous informerons par email et/ou via une notification sur la plateforme.
        </p>
    </x-legal-section>

    <x-legal-section number="12" title="Contact">
        <p>Pour toute question concernant cette politique de confidentialité :</p>
        <ul>
            <li><strong>Email :</strong> privacy@azohub.bj</li>
            <li><strong>Formulaire de contact :</strong> <a href="{{ route('contact') }}">Nous contacter</a></li>
        </ul>
    </x-legal-section>

    <x-legal-section number="13" title="Réclamation">
        <p>
            Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une
            réclamation auprès de l'autorité de protection des données compétente au Bénin.
        </p>
    </x-legal-section>

    <div class="flex items-start gap-4 rounded-xl bg-forest-600/10 p-6">
        <x-app-icon name="check-circle" class="mt-0.5 h-6 w-6 flex-shrink-0 text-forest-700" />
        <div>
            <p class="font-bold text-forest-700">Notre engagement</p>
            <p class="mt-1 text-forest-700">
                Nous nous engageons à protéger vos données personnelles et à respecter votre vie privée.
                La confiance que vous nous accordez est notre priorité.
            </p>
        </div>
    </div>

    <div class="flex items-start gap-4 rounded-xl bg-terracotta-50 p-6">
        <x-app-icon name="book" class="mt-0.5 h-6 w-6 flex-shrink-0 text-terracotta-700" />
        <div>
            <p class="font-bold text-terracotta-700">Documents connexes</p>
            <p class="mt-1 text-terracotta-700">
                <a href="{{ route('terms') }}" class="font-bold underline">Conditions Générales d'Utilisation</a>
                &middot;
                <a href="{{ route('faq') }}" class="font-bold underline">Foire Aux Questions</a>
            </p>
        </div>
    </div>
</x-legal-page>
