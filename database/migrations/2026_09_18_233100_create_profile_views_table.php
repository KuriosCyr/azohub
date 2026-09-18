<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('viewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['prestataire_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
    }
};
