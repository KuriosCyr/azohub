<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Zones d'intervention du service : liste de communes, ou « tout le Bénin ».
            // Sans zone (anciens services), le service se limite à sa ville.
            $table->json('service_areas')->nullable()->after('city');
            $table->boolean('serves_nationwide')->default(false)->after('service_areas');
            // Ancienne colonne texte, jamais alimentée par l'application.
            $table->dropColumn('service_area');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('service_area')->nullable()->after('city');
            $table->dropColumn(['service_areas', 'serves_nationwide']);
        });
    }
};
