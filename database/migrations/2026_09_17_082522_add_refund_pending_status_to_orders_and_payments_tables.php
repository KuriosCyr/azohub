<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'held', 'released', 'refund_pending', 'refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'success', 'failed', 'refund_pending', 'refunded') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE orders SET payment_status = 'refunded' WHERE payment_status = 'refund_pending'");
        DB::statement("UPDATE payments SET status = 'refunded' WHERE status = 'refund_pending'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'held', 'released', 'refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");
    }
};
