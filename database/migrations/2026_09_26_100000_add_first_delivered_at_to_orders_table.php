<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('first_delivered_at');
        });
    }
};
