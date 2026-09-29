<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'expired' distinct de 'closed' (déjà utilisé quand une proposition est acceptée,
        // cf. Proposal::accept()) et de 'cancelled' (annulation active par le client) : une
        // demande qui expire faute de proposition n'est ni l'un ni l'autre.
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('open', 'closed', 'cancelled', 'expired') NOT NULL DEFAULT 'open'");

        Schema::table('service_requests', function (Blueprint $table) {
            // Empêche de relancer le client à chaque exécution du scheduler tant qu'aucune
            // proposition n'est arrivée entre-temps — une seule relance par demande.
            $table->timestamp('stale_reminded_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('stale_reminded_at');
        });

        DB::statement("UPDATE service_requests SET status = 'cancelled' WHERE status = 'expired'");
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open'");
    }
};
