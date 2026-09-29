<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Part du crédit de parrainage du client consommée sur cette commande précise —
            // voir PaymentService::initiateForOrder() (application) et Order::refund() (restitution
            // si la commande n'a finalement jamais été payée).
            $table->decimal('referral_credit_applied', 10, 2)->default(0)->after('client_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('referral_credit_applied');
        });
    }
};
