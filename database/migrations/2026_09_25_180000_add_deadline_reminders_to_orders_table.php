<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Évitent de renvoyer le même rappel plusieurs fois (la commande commande passe
            // toutes les 15 minutes sur la fenêtre 12h/1h avant l'échéance de livraison).
            $table->timestamp('deadline_reminded_12h_at')->nullable()->after('expected_delivery_at');
            $table->timestamp('deadline_reminded_1h_at')->nullable()->after('deadline_reminded_12h_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['deadline_reminded_12h_at', 'deadline_reminded_1h_at']);
        });
    }
};
