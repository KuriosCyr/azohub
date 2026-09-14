<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('accepted_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->boolean('revision_requested')->default(false)->after('delivery_note');
            $table->text('revision_notes')->nullable()->after('revision_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_at',
                'cancelled_at',
                'cancellation_reason',
                'revision_requested',
                'revision_notes',
            ]);
        });
    }
};
