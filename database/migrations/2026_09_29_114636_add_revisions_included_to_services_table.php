<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Nombre de révisions incluses dans le prix du service, défini par le prestataire.
            // Recopié sur la commande à sa création (comme le prix) : une modification ultérieure
            // du service n'affecte jamais les commandes déjà en cours.
            $table->unsignedTinyInteger('revisions_included')->default(2)->after('delivery_time');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('revisions_included');
        });
    }
};
