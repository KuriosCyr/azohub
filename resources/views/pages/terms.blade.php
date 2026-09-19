<x-public-layout>
    <div class="min-h-screen bg-cream">
        {{-- Hero Section --}}
        <div class="bg-ink-900 text-cream-50 py-16">
            <div class="container mx-auto px-4 text-center">
                <h1 class="font-serif text-4xl md:text-5xl font-medium mb-4">
                    <x-app-icon name="clipboard" class="w-8 h-8 inline-block" /> Conditions Générales d'Utilisation
                </h1>
                <p class="text-lg text-cream-100/70">
                    Dernière mise à jour : {{ date('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="container mx-auto px-4 py-12">
            <div class="max-w-4xl mx-auto bg-cream-50 border border-ink-100 rounded-xl p-8 md:p-12 prose prose-lg max-w-none">
                
                <h2>1. Présentation de la plateforme</h2>
                <p>
                    Azohub est une plateforme numérique opérant au Bénin, qui met en relation des clients recherchant 
                    des services avec des prestataires qualifiés. La plateforme est accessible via le site web 
                    <strong>azohub.bj</strong>.
                </p>

                <h2>2. Acceptation des conditions</h2>
                <p>
                    En accédant et en utilisant Azohub, vous acceptez d'être lié par les présentes Conditions Générales 
                    d'Utilisation. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre plateforme.
                </p>

                <h2>3. Inscription et compte utilisateur</h2>
                
                <h3>3.1 Conditions d'inscription</h3>
                <ul>
                    <li>Vous devez avoir au moins 18 ans pour créer un compte</li>
                    <li>Les informations fournies doivent être exactes et à jour</li>
                    <li>Vous êtes responsable de la confidentialité de vos identifiants</li>
                    <li>Un utilisateur ne peut créer qu'un seul compte</li>
                </ul>

                <h3>3.2 Types de comptes</h3>
                <ul>
                    <li><strong>Compte Client :</strong> Pour commander des services</li>
                    <li><strong>Compte Prestataire :</strong> Pour proposer des services</li>
                    <li>Un utilisateur peut avoir les deux rôles simultanément</li>
                </ul>

                <h2>4. Utilisation de la plateforme</h2>
                
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

                <h2>5. Transactions et paiements</h2>
                
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

                <h2>6. Retraits (pour les prestataires)</h2>
                <ul>
                    <li>Retrait minimum : 1 000 FCFA</li>
                    <li>Délai de traitement : 24-48h ouvrées</li>
                    <li>Modes de retrait : Mobile Money (MTN, Moov, Celtiis Cash)</li>
                    <li>Les retraits sont soumis à vérification d'identité</li>
                </ul>

                <h2>7. Annulations et litiges</h2>
                
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

                <h2>8. Propriété intellectuelle</h2>
                <ul>
                    <li>Le contenu de la plateforme est protégé par les droits d'auteur</li>
                    <li>Les prestataires conservent les droits sur leurs créations</li>
                    <li>Le client obtient une licence d'utilisation après paiement complet</li>
                    <li>Azohub se réserve le droit d'utiliser les contenus à des fins promotionnelles</li>
                </ul>

                <h2>9. Responsabilités</h2>
                
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

                <h2>10. Comportements interdits</h2>
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

                <h2>11. Suspension et résiliation</h2>
                <p>
                    Azohub se réserve le droit de suspendre ou de résilier tout compte en cas de violation 
                    des présentes conditions, sans préavis et sans remboursement.
                </p>

                <h2>12. Modifications des conditions</h2>
                <p>
                    Azohub peut modifier ces conditions à tout moment. Les utilisateurs seront informés des 
                    changements significatifs. L'utilisation continue de la plateforme après modification 
                    constitue une acceptation des nouvelles conditions.
                </p>

                <h2>13. Protection des données</h2>
                <p>
                    Vos données personnelles sont traitées conformément à notre 
                    <a href="{{ route('privacy') }}" class="text-terracotta-600 hover:text-terracotta-700 font-bold">
                        Politique de Confidentialité
                    </a>.
                </p>

                <h2>14. Droit applicable et juridiction</h2>
                <p>
                    Les présentes conditions sont régies par le droit béninois. Tout litige relève de la 
                    compétence exclusive des tribunaux de Cotonou, Bénin.
                </p>

                <h2>15. Contact</h2>
                <p>
                    Pour toute question concernant ces conditions, contactez-nous :
                </p>
                <ul>
                    <li><strong>Email :</strong> legal@azohub.bj</li>
                    <li><strong>Adresse :</strong> Cotonou, Bénin</li>
                </ul>

                <div class="bg-terracotta-50 border-l-4 border-terracotta-600 p-6 rounded mt-8">
                    <p class="font-bold text-terracotta-700 mb-2"><x-app-icon name="lightbulb" class="w-5 h-5 inline-block align-text-bottom" /> Besoin d'aide ?</p>
                    <p class="text-terracotta-700">
                        Consultez notre <a href="{{ route('faq') }}" class="underline font-bold">FAQ</a>
                        ou <a href="{{ route('contact') }}" class="underline font-bold">contactez notre support</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>