<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('max_uses');
            // La commande doit atteindre au moins ce montant (avant réduction) pour que le code
            // s'applique — voir Order::applyPromoCode().
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'min_order_amount']);
        });
    }
};
