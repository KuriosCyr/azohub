<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // AZH-2025-00001
            
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_request_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('set null');
            
            $table->text('requirements')->nullable(); // Détails/instructions du client
            
            // Prix
            $table->decimal('amount', 10, 2); // Montant total
            $table->decimal('commission', 10, 2)->default(0.00); // Commission Azohub
            $table->decimal('prestataire_amount', 10, 2); // Montant pour le prestataire
            
            // Délais
            $table->integer('delivery_time'); // Délai convenu
            $table->timestamp('expected_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Statuts
            $table->enum('status', [
                'pending_payment',
                'paid',
                'in_progress',
                'delivered',
                'completed',
                'cancelled',
                'disputed'
            ])->default('pending_payment');
            
            $table->enum('payment_status', [
                'pending',
                'held', // Escrow
                'released',
                'refunded'
            ])->default('pending');
            
            // Livraison
            $table->json('deliverables')->nullable(); // Fichiers livrés
            $table->text('delivery_note')->nullable(); // Note du prestataire
            
            // Validation
            $table->timestamp('validation_deadline')->nullable(); // Date limite validation (72h)
            $table->timestamp('validated_at')->nullable();
            $table->boolean('auto_validated')->default(false);
            
            $table->timestamps();
            
            $table->index(['client_id', 'prestataire_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};