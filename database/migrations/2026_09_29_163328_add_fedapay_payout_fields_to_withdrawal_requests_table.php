<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('fedapay_payout_id')->nullable()->after('payment_method');
            // Dernier statut connu CÔTÉ FEDAPAY (pending/sent/failed...), distinct du statut
            // Azohub (status) qui reste pending/paid/rejected — permet à l'admin de voir "FedaPay
            // dit : en cours" sans confondre avec le statut métier qui, lui, ne change qu'une
            // fois la confirmation reçue (webhook) ou l'action manuelle prise.
            $table->string('fedapay_status')->nullable()->after('fedapay_payout_id');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn(['fedapay_payout_id', 'fedapay_status']);
        });
    }
};
