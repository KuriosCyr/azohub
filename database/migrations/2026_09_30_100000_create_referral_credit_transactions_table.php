<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Corrige un point signalé par un audit externe : contrairement au portefeuille réel
// (wallet_transactions), le crédit de parrainage n'était ni journalisé ni visible dans l'admin
// — seule la colonne users.referral_credit_balance était modifiée, sans aucune trace.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earned', 'redeemed', 'refunded']);
            $table->decimal('amount', 10, 2);
            // Solde APRÈS cette opération — même principe que wallet_transactions.balance_after.
            $table->decimal('balance_after', 10, 2);
            $table->string('reason');
            // Polymorphe informel comme wallet_transactions.source : le filleul dont
            // l'identité vient d'être approuvée ("earned"), ou la commande/l'abonnement sur
            // lequel le crédit a été dépensé ou restitué ("redeemed"/"refunded").
            $table->nullableMorphs('source');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_credit_transactions');
    }
};
