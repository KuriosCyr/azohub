<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->unique()->after('fedapay_customer_id');
            $table->foreignId('referred_by')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();
            // Séparé de wallet_balance (gains réels d'un prestataire, retirables) : un crédit de
            // parrainage n'est jamais retirable, seulement applicable en réduction sur une
            // prochaine commande — voir User::maybeRewardReferrer().
            $table->decimal('referral_credit_balance', 10, 2)->default(0)->after('referred_by');
            // Empêche qu'un même filleul déclenche la récompense de son parrain plus d'une fois
            // (peu importe combien de commandes il complète ensuite).
            $table->boolean('referral_reward_granted')->default(false)->after('referral_credit_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropColumn(['referral_code', 'referral_credit_balance', 'referral_reward_granted']);
        });
    }
};
