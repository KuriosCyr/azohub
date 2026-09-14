<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('opened_by')->constrained('users')->onDelete('cascade');
            
            $table->enum('reason', [
                'work_not_delivered',
                'work_not_conform',
                'poor_quality',
                'late_delivery',
                'payment_issue',
                'other'
            ]);
            
            $table->text('description');
            $table->json('evidences')->nullable(); // Preuves (captures, fichiers)
            
            $table->enum('status', ['open', 'under_review', 'resolved', 'cancelled'])->default('open');
            
            $table->enum('resolution', [
                'refund_client',
                'pay_prestataire',
                'partial_refund',
                'no_action'
            ])->nullable();
            
            $table->text('admin_note')->nullable(); // Note de l'admin
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};