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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('identity_verification_status', ['none', 'pending', 'verified', 'rejected'])
                ->default('none')
                ->after('identity_document');
            $table->text('identity_rejection_reason')->nullable()->after('identity_verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['identity_verification_status', 'identity_rejection_reason']);
        });
    }
};
