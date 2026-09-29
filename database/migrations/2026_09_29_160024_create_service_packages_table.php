<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->enum('tier', ['basic', 'standard', 'premium']);
            // Prix/délai/révisions propres à cette formule — un service qui ne propose pas de
            // formules garde simplement 0 ligne ici et continue d'utiliser ses propres
            // price/delivery_time/revisions_included (cf. Service::hasPackages()).
            $table->decimal('price', 10, 2);
            $table->integer('delivery_time');
            $table->unsignedTinyInteger('revisions_included')->default(2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
