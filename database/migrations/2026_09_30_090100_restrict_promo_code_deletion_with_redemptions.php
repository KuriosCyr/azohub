<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Corrige un point signalé par un audit externe : supprimer un code promo effaçait en cascade
// tout son historique d'utilisation (promo_code_redemptions), le rendant de nouveau utilisable
// par tout le monde — l'admin ne peut désormais plus supprimer un code du tout (seulement le
// désactiver via is_active, voir EditPromoCode/PromoCodesTable), mais cette contrainte protège
// aussi au niveau base de données contre toute suppression, y compris manuelle.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_code_redemptions', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
        });

        Schema::table('promo_code_redemptions', function (Blueprint $table) {
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('promo_code_redemptions', function (Blueprint $table) {
            $table->dropForeign(['promo_code_id']);
        });

        Schema::table('promo_code_redemptions', function (Blueprint $table) {
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->cascadeOnDelete();
        });
    }
};
