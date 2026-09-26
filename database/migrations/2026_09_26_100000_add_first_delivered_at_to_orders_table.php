<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Contrairement à delivered_at (écrasé à chaque re-livraison après une révision),
            // ce champ n'est renseigné qu'une seule fois — c'est lui qui sert de référence pour
            // juger la ponctualité du prestataire : une re-livraison après révision ne doit pas
            // artificiellement faire paraître la commande "en retard" alors que le premier envoi
            // respectait le délai.
            $table->timestamp('first_delivered_at')->nullable()->after('expected_delivery_at');
        });

        // Rétro-remplissage : sans lui, TOUTES les commandes déjà livrées avant cette migration
        // partiraient avec first_delivered_at NULL, donc 0 livraison "chronométrée" pour tous
        // les prestataires déjà actifs — ils démarreraient à 0% de ponctualité et pourraient être
        // rétrogradés dès leur prochaine commande, alors qu'ils ont un vrai historique de
        // livraisons à l'heure. delivered_at est la meilleure approximation disponible : il ne
        // correspond au premier envoi que pour les commandes jamais révisées, mais c'est le cas
        // de la grande majorité, et grandement préférable à une remise à zéro générale.
        DB::table('orders')
            ->whereNotNull('delivered_at')
            ->whereNull('first_delivered_at')
            ->update(['first_delivered_at' => DB::raw('delivered_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('first_delivered_at');
        });
    }
};
