<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Même principe que 'rating' (recalculé et mis en cache, pas recalculé à chaque
            // affichage) : ce taux gate désormais le niveau du prestataire (User::updateLevel()),
            // qui a besoin d'une valeur stable au même instant que completed_orders/rating.
            $table->decimal('on_time_delivery_rate', 5, 2)->nullable()->after('level');
            // Dénominateur du taux ci-dessus, gardé à part pour ne jamais afficher un
            // pourcentage sans dire sur combien de commandes il porte (5 minimum avant affichage
            // public — voir User::PUNCTUALITY_MIN_SAMPLE).
            $table->unsignedInteger('timed_deliveries_count')->default(0)->after('on_time_delivery_rate');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['on_time_delivery_rate', 'timed_deliveries_count']);
        });
    }
};
