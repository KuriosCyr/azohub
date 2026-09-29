<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->nullable()->after('referral_credit_applied')
                ->constrained()->nullOnDelete();
            // Mutuellement exclusif avec referral_credit_applied — voir Order::applyPromoCode()
            // et PaymentService::initiateForOrder().
            $table->decimal('promo_discount_applied', 10, 2)->default(0)->after('promo_code_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn('promo_discount_applied');
        });
    }
};
