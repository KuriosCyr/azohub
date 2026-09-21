<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            // GÉNÉRAL
            [
                'category' => 'general',
                'question' => 'Qu\'est-ce qu\'Azohub ?',
                'answer' => 'Azohub est la première plateforme béninoise de mise en relation entre clients et prestataires de services. Nous connectons les personnes qui ont besoin de services (plomberie, design, développement, etc.) avec des professionnels qualifiés.',
                'order' => 1,
            ],
            [
                'category' => 'general',
                'question' => 'Comment fonctionne Azohub ?',
                'answer' => 'C\'est simple ! Les prestataires créent des services avec des prix fixes. Les clients parcourent les services, passent commande et paient en ligne. Les prestataires livrent le travail, et une fois validé, ils reçoivent leur paiement.',
                'order' => 2,
            ],
            [
                'category' => 'general',
                'question' => 'Azohub est-il gratuit ?',
                'answer' => 'L\'inscription est gratuite pour tous. Le client paie des frais de service de 5 % en plus du prix affiché. Le prestataire paie une commission de 5 % à 15 % (selon son niveau et son abonnement, et 10 % pendant ses 3 premiers mois), uniquement lorsqu\'une commande est finalisée avec succès.',
                'order' => 3,
            ],
            [
                'category' => 'general',
                'question' => 'Dans quelles villes Azohub est-il disponible ?',
                'answer' => 'Azohub est actuellement disponible dans tout le Bénin. Les services peuvent être proposés en ligne ou en présentiel selon la nature du service.',
                'order' => 4,
            ],

            // PRESTATAIRES
            [
                'category' => 'prestataire',
                'question' => 'Comment devenir prestataire sur Azohub ?',
                'answer' => 'Créez un compte, complétez votre profil, et créez votre premier service. C\'est tout ! Votre service sera visible immédiatement après publication.',
                'order' => 1,
            ],
            [
                'category' => 'prestataire',
                'question' => 'Combien puis-je gagner sur Azohub ?',
                'answer' => 'Vos gains dépendent du prix de vos services et du nombre de commandes. Azohub prélève sur chaque vente une commission de 5 % à 15 % selon votre niveau et votre abonnement (10 % pendant vos 3 premiers mois) : vous recevez le reste. Il n\'y a pas de limite de gains !',
                'order' => 2,
            ],
            [
                'category' => 'prestataire',
                'question' => 'Comment retirer mes gains ?',
                'answer' => 'Vous pouvez retirer vos gains dès que votre solde atteint 2 000 FCFA. Les retraits sont effectués par Mobile Money (MTN, Moov, Celtiis Cash) sous 24-48h ouvrées.',
                'order' => 3,
            ],
            [
                'category' => 'prestataire',
                'question' => 'Puis-je modifier mes prix après publication ?',
                'answer' => 'Oui, vous pouvez modifier vos services à tout moment depuis votre dashboard. Les nouvelles commandes seront au nouveau prix, mais les commandes en cours gardent l\'ancien prix.',
                'order' => 4,
            ],
            [
                'category' => 'prestataire',
                'question' => 'Comment gérer les révisions demandées par les clients ?',
                'answer' => 'Lorsqu\'un client demande une révision, vous recevez une notification avec les détails. Effectuez les modifications demandées et marquez à nouveau le travail comme livré.',
                'order' => 5,
            ],

            // CLIENTS
            [
                'category' => 'client',
                'question' => 'Comment passer une commande ?',
                'answer' => 'Parcourez les services, choisissez celui qui vous convient, cliquez sur "Commander" et suivez les étapes de paiement. Vous serez mis en contact avec le prestataire automatiquement.',
                'order' => 1,
            ],
            [
                'category' => 'client',
                'question' => 'Puis-je annuler une commande ?',
                'answer' => 'Vous pouvez annuler une commande avant qu\'elle ne soit acceptée par le prestataire. Après acceptation, contactez le prestataire pour discuter d\'une éventuelle annulation. En cas de litige, notre équipe peut intervenir.',
                'order' => 2,
            ],
            [
                'category' => 'client',
                'question' => 'Que se passe-t-il si je ne suis pas satisfait du travail ?',
                'answer' => 'Vous pouvez demander des révisions gratuites selon les conditions du service. Si le problème persiste, vous pouvez ouvrir un litige et notre équipe examinera le cas pour trouver une solution équitable.',
                'order' => 3,
            ],
            [
                'category' => 'client',
                'question' => 'Comment contacter le prestataire ?',
                'answer' => 'Une fois la commande passée, vous avez accès à une messagerie directe avec le prestataire depuis la page de votre commande. Vous pouvez échanger des messages et partager des fichiers.',
                'order' => 4,
            ],

            // PAIEMENTS
            [
                'category' => 'paiement',
                'question' => 'Quels moyens de paiement sont acceptés ?',
                'answer' => 'Nous acceptons Mobile Money (MTN, Moov), les cartes bancaires Visa/Mastercard via notre partenaire de paiement sécurisé FedaPay.',
                'order' => 1,
            ],
            [
                'category' => 'paiement',
                'question' => 'Mon paiement est-il sécurisé ?',
                'answer' => 'Oui ! Tous les paiements sont traités via des passerelles sécurisées certifiées. Nous ne stockons jamais vos informations bancaires. Votre argent est gardé en sécurité jusqu\'à la livraison du service.',
                'order' => 2,
            ],
            [
                'category' => 'paiement',
                'question' => 'Quand le prestataire reçoit-il son paiement ?',
                'answer' => 'Le prestataire reçoit son paiement uniquement après que vous ayez validé la livraison du service. Cela garantit que vous êtes satisfait avant que l\'argent ne soit transféré.',
                'order' => 3,
            ],
            [
                'category' => 'paiement',
                'question' => 'Puis-je être remboursé ?',
                'answer' => 'Oui, en cas d\'annulation par le prestataire ou de litige résolu en votre faveur, vous serez intégralement remboursé. Le remboursement est effectué dans un délai de 3-7 jours ouvrés.',
                'order' => 4,
            ],

            // SÉCURITÉ
            [
                'category' => 'securite',
                'question' => 'Mes données personnelles sont-elles protégées ?',
                'answer' => 'Absolument. Nous utilisons le cryptage SSL et suivons les meilleures pratiques de sécurité. Vos données ne sont jamais partagées avec des tiers sans votre consentement.',
                'order' => 1,
            ],
            [
                'category' => 'securite',
                'question' => 'Comment vérifiez-vous l\'identité des prestataires ?',
                'answer' => 'Nous vérifions l\'identité des prestataires via leurs documents officiels (CNI, passeport). Les prestataires vérifiés ont un badge spécial sur leur profil.',
                'order' => 2,
            ],
            [
                'category' => 'securite',
                'question' => 'Que faire en cas de comportement suspect ?',
                'answer' => 'Signalez immédiatement tout comportement inapproprié via le bouton de signalement ou contactez notre support. Nous prenons toutes les plaintes au sérieux et agissons rapidement.',
                'order' => 3,
            ],
            [
                'category' => 'securite',
                'question' => 'Puis-je changer mon mot de passe ?',
                'answer' => 'Oui, vous pouvez changer votre mot de passe à tout moment depuis les paramètres de votre compte. Nous vous recommandons d\'utiliser un mot de passe fort et unique.',
                'order' => 4,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}