<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Traçabilité : quelle formule (Basique/Standard/Premium) a été commandée. Nullable
            // pour les commandes sur un service sans formules, ou négociées (proposition/offre).
            $table->foreignId('service_package_id')->nullable()->after('service_id')
                ->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_package_id');
        });
    }
};
