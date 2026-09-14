<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Client
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 10, 2)->nullable(); // Budget indicatif
            $table->integer('deadline')->nullable(); // Délai souhaité en jours
            $table->string('city');
            $table->string('address')->nullable();
            
            $table->json('attachments')->nullable(); // Photos/documents
            
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('open');
            $table->integer('proposals_count')->default(0); // Nombre de propositions reçues
            
            $table->timestamp('expires_at')->nullable(); // Date d'expiration de la demande
            $table->timestamps();
            
            $table->index(['user_id', 'category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};