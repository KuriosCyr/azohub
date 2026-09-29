<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Utilisé par Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication
            // (double authentification par email du panel admin, rendue obligatoire —
            // AdminPanelProvider::multiFactorAuthentication(..., isRequired: true)).
            $table->boolean('has_email_authentication')->default(false)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_email_authentication');
        });
    }
};
