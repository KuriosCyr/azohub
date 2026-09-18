<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Frais de service prélevés sur le CLIENT (taux fixe, indépendant du
            // niveau du prestataire), en plus de la commission déjà prélevée sur
            // le prestataire (amount - commission = prestataire_amount).
            $table->decimal('client_fee', 10, 2)->default(0)->after('commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('client_fee');
        });
    }
};
