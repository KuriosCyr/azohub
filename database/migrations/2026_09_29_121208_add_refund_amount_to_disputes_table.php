<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            // Renseigné uniquement pour resolution = 'partial_refund' : le montant exact rendu
            // au client (le prestataire reçoit automatiquement le reste sur son portefeuille).
            // Sans ce champ, "remboursement partiel" n'avait aucun moyen d'exister autrement
            // qu'en note libre — il déclenchait en réalité le même remboursement à 100% que
            // "Rembourser le client".
            $table->decimal('refund_amount', 10, 2)->nullable()->after('resolution');
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn('refund_amount');
        });
    }
};
