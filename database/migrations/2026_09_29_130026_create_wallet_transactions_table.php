<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 10, 2);
            // Solde APRÈS cette opération : seule colonne wallet_balance sur users était
            // incrémentée/décrémentée jusqu'ici, sans aucune trace pour reconstituer un
            // historique en cas d'écart constaté (utile pour la comptabilité et les litiges).
            $table->decimal('balance_after', 10, 2);
            $table->string('reason');
            // Polymorphe informel plutôt que morphs() : la commande ou la demande de retrait à
            // l'origine du mouvement, quand il y en a une (un ajustement manuel futur n'en aurait
            // pas forcément).
            $table->nullableMorphs('source');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
