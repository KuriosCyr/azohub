<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Prestataire
            
            $table->text('message'); // Message personnalisé
            $table->decimal('proposed_price', 10, 2); // Prix proposé
            $table->integer('delivery_time'); // Délai proposé en jours
            
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->index(['service_request_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};