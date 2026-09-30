<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Corrige un point signalé par un 2e audit externe : contrairement aux commandes, les abonnements
// pouvaient être supprimés définitivement (individuellement ou en masse) sans aucune protection —
// ce qui effaçait en cascade leurs paiements (onDelete('cascade') sur payments.subscription_id).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
