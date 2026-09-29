<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_offers', function (Blueprint $table) {
            // Nullable : une offre créée avant ce champ (ou sans valeur explicite) retombe sur
            // Order::DEFAULT_REVISIONS_INCLUDED au moment de la commande plutôt que sur 0.
            $table->unsignedTinyInteger('revisions_included')->nullable()->after('delivery_days');
        });
    }

    public function down(): void
    {
        Schema::table('custom_offers', function (Blueprint $table) {
            $table->dropColumn('revisions_included');
        });
    }
};
