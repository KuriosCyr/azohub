<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Réponse facultative du prestataire à un avis client -> prestataire.
            // Une seule, jamais modifiable une fois publiée (pas de colonne "edited_at").
            $table->text('response')->nullable()->after('comment');
            $table->timestamp('responded_at')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['response', 'responded_at']);
        });
    }
};
