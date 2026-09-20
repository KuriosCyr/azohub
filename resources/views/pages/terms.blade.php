<x-legal-page
    title="Conditions Générales d'Utilisation"
    subtitle="Les règles qui encadrent l'utilisation d'Azohub pour les clients et les prestataires : inscription, paiements, retraits, litiges."
    updated="20/09/2026"
    other-label="Politique de confidentialité"
    :other-route="route('privacy')">

    {{-- L'essentiel --}}
    <div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-ink-400">L'essentiel en un coup d'œil</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="chart-bar" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">5 % à 15 %</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">de commission sur le prestataire, plus faible avec le niveau et l'abonnement.</p>
            </div>
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="cart" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">5 % de frais client</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">ajoutés au prix affiché, quel que soit le prestataire.</p>
            </div>
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="lock" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">Paiement protégé</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">conservé par Azohub, versé au prestataire après votre validation.</p>
            </div>
            <div class="rounded-xl border border-ink-100 bg-cream-50 p-5">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-terracotta-50 text-terracotta-700">
                    <x-app-icon name="banknotes" class="h-5 w-5" />
                </div>
                <p class="font-serif text-xl font-medium text-ink-900">Retrait dès 1 000 FCFA</p>
                <p class="mt-1 text-sm leading-relaxed text-ink-500">par Mobile Money, traité sous 24 à 48 h ouvrées.</p>
            </div>
        </div>
    </div>

    <x-legal-section number="1" title="Présentation de la plateforme">
        <p>
            Azohub est une plateforme numérique opérant au Bénin, qui met en relation des clients recherchant
            des services avec des prestataires qualifiés. La plateforme est accessible via le site web
            <strong>azohub.bj</strong>.
        </p>
    </x-legal-section>

    <x-legal-section number="2" title="Acceptation des conditions">
        <p>
            En accédant et en utilisant Azohub, vous acceptez d'être lié par les présentes Conditions Générales
            d'Utilisation. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre plateforme.
        </p>
    </x-legal-section>

    <x-legal-section number="3" title="Inscription et compte utilisateur">
        <h3>3.1 Conditions d'inscription</h3>
        <ul>
            <li>Vous devez avoir au moins 18 ans pour créer un compte</li>
            <li>Les informations fournies doivent être exactes et à jour</li>
            <li>Vous êtes responsable de la confidentialité de vos identifiants</li>
            <li>Un utilisateur ne peut créer qu'un seul compte</li>
        </ul>

        <h3>3.2 Types de comptes</h3>
        <ul>
            <li><strong>Compte Client :</strong> pour commander des services</li>
            <li><strong>Compte Prestataire :</strong> pour proposer des services</li>
            <li>Un utilisateur peut avoir les deux rôles simultanément</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="4" title="Utilisation de la plateforme">
        <h3>4.1 Pour les clients</h3>
        <ul>
            <li>Vous pouvez parcourir les services sans inscription</li>
            <li>Un compte est requis pour passer commande</li>
            <li>Le paiement doit être effectué lors de la commande</li>
            <li>Vous devez valider la livraison après vérification du travail</li>
        </ul>

        <h3>4.2 Pour les prestataires</h3>
        <ul>
            <li>Vous devez créer des services avec des prix fixes</li>
            <li>Les délais de livraison annoncés doivent être respectés</li>
            <li>Vous devez accepter ou refuser les commandes sous 48h</li>
            <li>La qualité du travail doit correspondre à la description</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="5" title="Transactions et paiements">
        <h3>5.1 Prix et commissions</h3>
        <ul>
            <li>Les prix sont affichés en Francs CFA (FCFA)</li>
            <li>Azohub prélève sur le prestataire une commission comprise entre <strong>5% et 15%</strong>, selon son niveau de performance (nouveau, confirmé, expert) et son plan d'abonnement (Gratuit, Pro, Premium) — plus le niveau ou le plan est élevé, plus la commission est faible</li>
            <li>Un frais de service fixe de <strong>5%</strong> est ajouté au prix affiché et payé par le client, quel que soit le niveau du prestataire</li>
            <li>Les frais de transaction peuvent s'appliquer selon le mode de paiement</li>
        </ul>

        <h3>5.2 Processus de paiement</h3>
        <ul>
            <li>Le paiement est effectué lors de la commande</li>
            <li>L'argent est conservé en sécurité par Azohub</li>
            <li>Le prestataire reçoit le paiement après validation du client</li>
            <li>Les moyens de paiement acceptés : Mobile Money, Cartes bancaires</li>
        </ul>

        <h3>5.3 Remboursements</h3>
        <ul>
            <li>Remboursement complet si le prestataire refuse la commande</li>
            <li>Remboursement en cas de litige résolu en faveur du client</li>
            <li>Les remboursements sont traités sous 3-7 jours ouvrés</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="6" title="Retraits (pour les prestataires)">
        <ul>
            <li>Retrait minimum : 1 000 FCFA</li>
            <li>Délai de traitement : 24-48h ouvrées</li>
            <li>Modes de retrait : Mobile Money (MTN, Moov, Celtiis Cash)</li>
            <li>Les retraits sont soumis à vérification d'identité</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="7" title="Annulations et litiges">
        <h3>7.1 Annulation par le client</h3>
        <ul>
            <li>Annulation gratuite avant acceptation du prestataire</li>
            <li>Après acceptation, contactez le prestataire ou le support</li>
        </ul>

        <h3>7.2 Annulation par le prestataire</h3>
        <ul>
            <li>Le prestataire peut refuser une commande avant acceptation</li>
            <li>Après acceptation, l'annulation doit être justifiée</li>
            <li>Remboursement automatique du client en cas d'annulation</li>
        </ul>

        <h3>7.3 Gestion des litiges</h3>
        <ul>
            <li>En cas de désaccord, contactez d'abord le support Azohub</li>
            <li>Notre équipe de médiation examinera le cas</li>
            <li>La décision d'Azohub est finale</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="8" title="Propriété intellectuelle">
        <ul>
            <li>Le contenu de la plateforme est protégé par les droits d'auteur</li>
            <li>Les prestataires conservent les droits sur leurs créations</li>
            <li>Le client obtient une licence d'utilisation après paiement complet</li>
            <li>Azohub se réserve le droit d'utiliser les contenus à des fins promotionnelles</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="9" title="Responsabilités">
        <h3>9.1 Responsabilité d'Azohub</h3>
        <ul>
            <li>Azohub est un intermédiaire et ne garantit pas la qualité des services</li>
            <li>Nous ne sommes pas responsables des litiges entre utilisateurs</li>
            <li>La plateforme est fournie "en l'état"</li>
            <li>Nous nous efforçons d'assurer la sécurité des transactions</li>
        </ul>

        <h3>9.2 Responsabilité des utilisateurs</h3>
        <ul>
            <li>Les prestataires sont responsables de la qualité de leurs services</li>
            <li>Les clients doivent fournir des informations exactes</li>
            <li>Chaque utilisateur est responsable de ses actes sur la plateforme</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="10" title="Comportements interdits">
        <p>Il est strictement interdit de :</p>
        <ul>
            <li>Créer de faux comptes ou usurper l'identité d'autrui</li>
            <li>Publier du contenu offensant, illégal ou inapproprié</li>
            <li>Harceler ou menacer d'autres utilisateurs</li>
            <li>Tenter de contourner les systèmes de paiement</li>
            <li>Utiliser la plateforme pour des activités illégales</li>
            <li>Manipuler les avis ou les notes</li>
            <li>Extraire des données de la plateforme (scraping)</li>
        </ul>
    </x-legal-section>

    <x-legal-section number="11" title="Suspension et résiliation">
        <p>
            Azohub se réserve le droit de suspendre ou de résilier tout compte en cas de violation
            des présentes conditions, sans préavis et sans remboursement.
        </p>
    </x-legal-section>

    <x-legal-section number="12" title="Modifications des conditions">
        <p>
            Azohub peut modifier ces conditions à tout moment. Les utilisateurs seront informés des
            changements significatifs. L'utilisation continue de la plateforme après modification
            constitue une acceptation des nouvelles conditions.
        </p>
    </x-legal-section>

    <x-legal-section number="13" title="Protection des données">
        <p>
            Vos données personnelles sont traitées conformément à notre
            <a href="{{ route('privacy') }}">Politique de Confidentialité</a>.
        </p>
    </x-legal-section>

    <x-legal-section number="14" title="Droit applicable et juridiction">
        <p>
            Les présentes conditions sont régies par le droit béninois. Tout litige relève de la
            compétence exclusive des tribunaux de Cotonou, Bénin.
        </p>
    </x-legal-section>

    <x-legal-section number="15" title="Contact">
        <p>Pour toute question concernant ces conditions, contactez-nous :</p>
        <ul>
            <li><strong>Email :</strong> legal@azohub.bj</li>
            <li><strong>Adresse :</strong> Cotonou, Bénin</li>
        </ul>
    </x-legal-section>

    <div class="flex items-start gap-4 rounded-xl bg-terracotta-50 p-6">
        <x-app-icon name="lightbulb" class="mt-0.5 h-6 w-6 flex-shrink-0 text-terracotta-700" />
        <div>
            <p class="font-bold text-terracotta-700">Besoin d'aide ?</p>
            <p class="mt-1 text-terracotta-700">
                Consultez notre <a href="{{ route('faq') }}" class="font-bold underline">FAQ</a>
                ou <a href="{{ route('contact') }}" class="font-bold underline">contactez notre support</a>.
            </p>
        </div>
    </div>
</x-legal-page>
