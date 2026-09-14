<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // ID transaction Mobile Money
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Payeur
            
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['mtn_momo', 'moov_money', 'celtiis_cash', 'card'])->default('mtn_momo');
            $table->string('phone_number')->nullable();
            
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->enum('type', ['order_payment', 'subscription', 'withdrawal'])->default('order_payment');
            
            $table->text('gateway_response')->nullable(); // Réponse de l'API de paiement
            $table->string('gateway_reference')->nullable(); // Référence externe
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};