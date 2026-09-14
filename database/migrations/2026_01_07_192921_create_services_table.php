<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Prestataire
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('what_included')->nullable(); // Ce qui est inclus
            
            $table->decimal('price', 10, 2); // Prix en FCFA
            $table->enum('price_type', ['fixe', 'a_partir_de'])->default('fixe');
            
            $table->integer('delivery_time'); // Délai en jours
            $table->string('city')->nullable(); // Ville du prestataire
            $table->text('service_area')->nullable(); // Zones couvertes
            
            $table->string('cover_image')->nullable(); // Image principale
            
            // Statistiques
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_orders')->default(0);
            $table->integer('total_reviews')->default(0);
            
            $table->enum('status', ['draft', 'active', 'paused', 'rejected'])->default('active');
            $table->boolean('is_featured')->default(false); // Service mis en avant
            
            $table->timestamps();
            
            // Index pour les recherches
            $table->index(['user_id', 'category_id', 'status']);
            $table->fullText(['title', 'description']); // Recherche full-text
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};