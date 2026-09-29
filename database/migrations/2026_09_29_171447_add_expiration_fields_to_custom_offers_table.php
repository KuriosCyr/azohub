<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'expired' distinct de 'declined' : le client n'a pas activement refusé l'offre, elle a
        // simplement expiré faute de réponse — un badge "Déclinée" aurait été trompeur.
        DB::statement("ALTER TABLE custom_offers MODIFY COLUMN status ENUM('pending', 'accepted', 'declined', 'expired') NOT NULL DEFAULT 'pending'");

        Schema::table('custom_offers', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('revisions_included');
        });
    }

    public function down(): void
    {
        Schema::table('custom_offers', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        DB::statement("UPDATE custom_offers SET status = 'declined' WHERE status = 'expired'");
        DB::statement("ALTER TABLE custom_offers MODIFY COLUMN status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending'");
    }
};
