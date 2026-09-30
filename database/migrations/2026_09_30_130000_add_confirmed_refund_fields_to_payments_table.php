<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Corrige un point signalé par un 3e audit externe : Payment::confirmRefund() n'enregistrait ni
// qui avait confirmé le remboursement, ni quand — seule la visibilité du bouton admin (status ===
// 'refund_pending') protégeait l'action, sans aucune trace pour de l'argent sortant.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('confirmed_refund_by')->nullable()->after('refund_amount_due')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_refund_at')->nullable()->after('confirmed_refund_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_refund_by');
            $table->dropColumn('confirmed_refund_at');
        });
    }
};
