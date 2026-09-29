<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Créé une seule fois (à la première demande de retrait payée via l'API), puis
            // réutilisé pour tous les retraits suivants du même prestataire — évite de créer un
            // nouveau client FedaPay à chaque virement.
            $table->string('fedapay_customer_id')->nullable()->after('wallet_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fedapay_customer_id');
        });
    }
};
