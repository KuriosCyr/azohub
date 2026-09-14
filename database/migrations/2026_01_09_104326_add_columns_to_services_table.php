<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            $table->json('tags')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('status');
            $table->integer('orders_count')->default(0)->after('total_orders'); // Pour les compteurs rapides
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['tags', 'is_active', 'orders_count']);
        });
    }
};