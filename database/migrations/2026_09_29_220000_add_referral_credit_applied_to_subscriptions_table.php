<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Même principe que orders.referral_credit_applied : permet au prestataire de payer
            // son abonnement (partiellement ou en totalité) avec le crédit gagné en parrainant —
            // voir Subscription::applyReferralCredit().
            $table->decimal('referral_credit_applied', 10, 2)->default(0)->after('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('referral_credit_applied');
        });
    }
};
