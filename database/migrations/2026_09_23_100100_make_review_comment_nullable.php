<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Le formulaire (et la validation de ReviewController) laissent le commentaire facultatif,
    // mais la colonne était NOT NULL sans défaut : laisser le champ vide plantait l'enregistrement
    // de l'avis (erreur 500) au lieu de l'accepter comme prévu.
    public function up(): void
    {
        DB::statement('ALTER TABLE reviews MODIFY comment TEXT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE reviews SET comment = '' WHERE comment IS NULL");
        DB::statement('ALTER TABLE reviews MODIFY comment TEXT NOT NULL');
    }
};
