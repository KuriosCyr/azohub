<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['fixed', 'percentage']);
            // Montant en FCFA (type "fixed") ou points de pourcentage (type "percentage") —
            // voir PromoCode::discountFor().
            $table->decimal('value', 10, 2);
            // null = pas de plafond d'utilisation.
            $table->unsignedInteger('max_uses')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
