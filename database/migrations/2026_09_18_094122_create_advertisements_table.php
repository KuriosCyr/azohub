<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Nom interne (repère admin)
            $table->string('advertiser_name');
            $table->string('advertiser_contact')->nullable();
            $table->string('image');
            $table->string('link_url');
            $table->enum('placement', ['home_banner', 'services_sidebar'])->default('home_banner');
            $table->integer('order')->default(0);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->decimal('price_paid', 10, 2)->nullable(); // Pour le suivi manuel côté admin
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['placement', 'is_active', 'starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
