<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Même logique que deadline_reminded_12h_at / deadline_reminded_1h_at : garantit
            // qu'une commande n'est notifiée qu'une seule fois une fois son délai dépassé,
            // quel que soit le nombre de passages de la commande planifiée.
            $table->timestamp('deadline_overdue_notified_at')->nullable()->after('deadline_reminded_1h_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('deadline_overdue_notified_at');
        });
    }
};
