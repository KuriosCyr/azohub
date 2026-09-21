<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nouveau statut « pending » : service (nouveau ou modifié) en attente de modération. Le défaut
        // reste « active » (seed/tests) : c'est le code applicatif qui place les services en modération.
        DB::statement("ALTER TABLE services MODIFY status ENUM('draft', 'pending', 'active', 'paused', 'rejected') NOT NULL DEFAULT 'active'");

        Schema::table('services', function (Blueprint $table) {
            $table->text('moderation_note')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('moderation_note');
        });

        // Les anciens brouillons (jamais utilisés par l'application) rejoignent la file de modération.
        DB::table('services')->where('status', 'draft')->update(['status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['moderation_note', 'reviewed_at']);
        });

        DB::table('services')->where('status', 'pending')->update(['status' => 'draft']);
        DB::statement("ALTER TABLE services MODIFY status ENUM('draft', 'active', 'paused', 'rejected') NOT NULL DEFAULT 'active'");
    }
};
