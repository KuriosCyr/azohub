<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // Qui note
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade'); // Qui est noté
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            
            $table->tinyInteger('rating'); // 1-5 étoiles
            $table->text('comment');
            
            // Critères spécifiques (pour prestataires)
            $table->tinyInteger('quality_rating')->nullable(); // Qualité du travail
            $table->tinyInteger('communication_rating')->nullable(); // Communication
            $table->tinyInteger('deadline_rating')->nullable(); // Respect des délais
            
            // Critères spécifiques (pour clients)
            $table->tinyInteger('clarity_rating')->nullable(); // Clarté de la demande
            $table->tinyInteger('responsiveness_rating')->nullable(); // Réactivité
            
            $table->enum('review_type', ['client_to_prestataire', 'prestataire_to_client']);
            
            $table->boolean('is_visible')->default(true); // Modération possible
            $table->timestamps();
            
            $table->index(['order_id', 'reviewer_id', 'reviewee_id']);
            
            // Un utilisateur ne peut noter qu'une fois par commande
            $table->unique(['order_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};