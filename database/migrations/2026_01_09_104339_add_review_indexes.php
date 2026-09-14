<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note: On garde reviewer_id et reviewee_id car c'est plus flexible
        // On ajoutera juste des accesseurs dans le modèle Review
        
        Schema::table('reviews', function (Blueprint $table) {
            // Ajouter un index pour les recherches
            $table->index(['reviewee_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['reviewee_id', 'is_visible']);
        });
    }
};