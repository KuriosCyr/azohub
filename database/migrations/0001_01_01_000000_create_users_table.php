<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            
            // Type d'utilisateur
            $table->enum('role', ['client', 'prestataire', 'admin'])->default('client');
            
            // Informations prestataire
            $table->text('bio')->nullable(); // Description du prestataire
            $table->string('city')->nullable(); // Ville parmi les 77 communes du Bénin
            $table->json('service_areas')->nullable(); // Zones d'intervention (array de villes)
            $table->json('languages')->nullable(); // Langues parlées
            $table->string('availability')->default('disponible'); // disponible, occupé, absent
            
            // Système de notation
            $table->decimal('rating', 3, 2)->default(0); // Note moyenne (0.00 à 5.00)
            $table->integer('total_reviews')->default(0); // Nombre total d'avis
            $table->integer('completed_orders')->default(0); // Nombre de commandes terminées
            
            // Niveau du prestataire
            $table->enum('level', ['nouveau', 'confirme', 'expert'])->default('nouveau');
            
            // Badges
            $table->json('badges')->nullable(); // Array de badges gagnés
            
            // Vérification d'identité
            $table->boolean('identity_verified')->default(false);
            $table->string('identity_document')->nullable(); // Chemin du document CNI/Passeport
            
            // Portefeuille
            $table->decimal('wallet_balance', 10, 2)->default(0); // Solde en FCFA
            
            // Statut du compte
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Pour archiver au lieu de supprimer
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};