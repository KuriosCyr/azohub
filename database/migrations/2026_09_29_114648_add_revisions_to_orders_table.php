<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Figés sur la commande à sa création (comme le prix/la commission) : une révision
            // ultérieure du service ou de l'offre n'affecte jamais une commande déjà passée.
            $table->unsignedTinyInteger('revisions_included')->default(2)->after('revision_notes');
            $table->unsignedTinyInteger('revisions_used')->default(0)->after('revisions_included');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['revisions_included', 'revisions_used']);
        });
    }
};
