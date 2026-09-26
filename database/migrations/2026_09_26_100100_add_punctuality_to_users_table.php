<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Rétro-remplit le taux/compteur de chaque prestataire à partir de son historique
        // (backfillé par la migration précédente), SANS jamais toucher à son niveau actuel :
        // en SQL brut plutôt que via User::updatePunctuality() (qui appelle updateLevel())
        // exprès, pour ne prendre AUCUN risque de rétrograder un prestataire déjà établi sur
        // la seule base de ce rétro-remplissage — par exemple si ses commandes les plus
        // anciennes n'avaient pas encore expected_delivery_at (ajouté à une date ultérieure de
        // l'historique du projet) et donnent donc un échantillon incomplet. Le niveau ne sera
        // réévalué qu'à sa PROCHAINE vraie livraison, avec des données fraîches et complètes.
        DB::table('users')
            ->where('role', 'prestataire')
            ->orderBy('id')
            ->each(function ($user) {
                $orders = DB::table('orders')
                    ->where('prestataire_id', $user->id)
                    ->whereNotNull('first_delivered_at')
                    ->whereNotNull('expected_delivery_at')
                    ->get(['first_delivered_at', 'expected_delivery_at']);

                if ($orders->isEmpty()) {
                    return;
                }

                $onTime = $orders->filter(function ($order) {
                    return Carbon::parse($order->first_delivered_at)->lessThanOrEqualTo(
                        Carbon::parse($order->expected_delivery_at)->addHours(3)
                    );
                })->count();

                DB::table('users')->where('id', $user->id)->update([
                    'on_time_delivery_rate' => round($onTime / $orders->count() * 100, 2),
                    'timed_deliveries_count' => $orders->count(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['on_time_delivery_rate', 'timed_deliveries_count']);
        });
    }
};
