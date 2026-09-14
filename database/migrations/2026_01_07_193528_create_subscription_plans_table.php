<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Gratuit, Pro, Premium
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00); // Prix mensuel en FCFA
            $table->string('billing_period')->default('monthly'); // monthly, yearly
            $table->integer('max_services')->nullable(); // Nombre de services publiables (null = illimité)
            $table->decimal('commission_rate', 5, 2)->default(15.00); // Taux de commission en %
            $table->json('features')->nullable(); // Liste des fonctionnalités
            $table->boolean('is_popular')->default(false); // Badge "Populaire"
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};