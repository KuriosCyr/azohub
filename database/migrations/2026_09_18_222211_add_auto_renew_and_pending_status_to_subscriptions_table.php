<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('auto_renew')->default(false)->after('status');
        });

        // "pending" : abonnement créé en attente de confirmation du paiement FedaPay.
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('pending', 'active', 'expired', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE subscriptions SET status = 'cancelled' WHERE status = 'pending'");
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active'");

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('auto_renew');
        });
    }
};
