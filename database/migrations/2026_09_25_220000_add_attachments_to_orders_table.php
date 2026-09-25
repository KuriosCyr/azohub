<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Même format que ServiceRequest::attachments (name/path/size/uploaded_at) : le
            // client passant commande directement (sans négociation via une demande) n'avait
            // aucun moyen de joindre un fichier au moment de décrire ses besoins.
            $table->json('attachments')->nullable()->after('requirements');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
