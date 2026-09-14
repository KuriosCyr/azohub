<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('reason', [
                'inappropriate_content',
                'scam',
                'copyright_violation',
                'misleading_info',
                'poor_quality',
                'spam',
                'other'
            ]);
            
            $table->text('details')->nullable();
            
            $table->enum('status', [
                'pending',
                'reviewing',
                'resolved',
                'dismissed'
            ])->default('pending');
            
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['service_id', 'status']);
            $table->index(['reporter_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};