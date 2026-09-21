<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Prix pour 12 mois (null = pas d'offre annuelle pour ce plan).
            $table->decimal('yearly_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('billing_period')->default('monthly')->after('status'); // monthly | yearly
            $table->boolean('is_trial')->default(false)->after('billing_period'); // mois d'essai offert
        });

        // Nouveaux tarifs de lancement (les plans existants sont mis à jour par slug).
        $json = fn (array $features) => json_encode($features, JSON_UNESCAPED_UNICODE);

        DB::table('subscription_plans')->where('slug', 'gratuit')->update([
            'features' => $json([
                '6 à 15 services selon votre niveau',
                'Commission de 15 % (10 % pendant vos 3 premiers mois)',
                'Support par email',
                'Profil de base',
            ]),
        ]);

        DB::table('subscription_plans')->where('slug', 'pro')->update([
            'price' => 3000,
            'yearly_price' => 30000,
        ]);

        DB::table('subscription_plans')->where('slug', 'premium')->update([
            'price' => 9000,
            'yearly_price' => 90000,
        ]);

        // La FAQ affichait une commission de 10 % et un retrait dès 10 000 FCFA : faux.
        $faq = [
            "L'inscription est gratuite pour tous. Azohub prélève une commission de 10% sur chaque transaction uniquement lorsqu'une commande est finalisée avec succès."
                => "L'inscription est gratuite pour tous. Le client paie des frais de service de 5 % en plus du prix affiché. Le prestataire paie une commission de 5 % à 15 % (selon son niveau et son abonnement, et 10 % pendant ses 3 premiers mois), uniquement lorsqu'une commande est finalisée avec succès.",
            "Vos gains dépendent du prix de vos services et du nombre de commandes. Après la commission de 10% d'Azohub, vous recevez 90% du montant de chaque vente. Il n'y a pas de limite de gains !"
                => "Vos gains dépendent du prix de vos services et du nombre de commandes. Azohub prélève sur chaque vente une commission de 5 % à 15 % selon votre niveau et votre abonnement (10 % pendant vos 3 premiers mois) : vous recevez le reste. Il n'y a pas de limite de gains !",
            "Vous pouvez retirer vos gains dès que votre solde atteint 10 000 FCFA. Les retraits sont effectués par Mobile Money (MTN, Moov) sous 24-48h ouvrées."
                => "Vous pouvez retirer vos gains dès que votre solde atteint 2 000 FCFA. Les retraits sont effectués par Mobile Money (MTN, Moov, Celtiis Cash) sous 24-48h ouvrées.",
        ];

        foreach ($faq as $old => $new) {
            DB::table('faqs')->where('answer', $old)->update(['answer' => $new]);
        }
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_period', 'is_trial']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('yearly_price');
        });
    }
};
