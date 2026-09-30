<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Corrige un point signalé par un audit externe : le tableau de bord financier comptait
// payments.amount (le montant total payé) comme "à rembourser", même pour un remboursement
// PARTIEL — surestimant ce qui reste réellement à traiter manuellement sur FedaPay. Ce nouveau
// champ porte le montant exact dû au client à ce titre, distinct du montant total payé.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('refund_amount_due', 10, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('refund_amount_due');
        });
    }
};
