<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Corrige un point signalé par un 2e audit externe : "Débloquer manuellement" (WithdrawalRequest::
// clearAmbiguousFedapayAttempt()) efface fedapay_payout_id — si le webhook "sent" de cet ancien
// virement arrive ensuite, plus rien ne permet de le relier à sa demande de retrait, et
// PaymentService::processPayoutUpdate() l'ignorait silencieusement. Historique séparé, jamais
// effacé, pour que ce cas précis puisse au moins être détecté et signalé à l'admin.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fedapay_payout_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_request_id')->constrained()->cascadeOnDelete();
            $table->string('fedapay_payout_id')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fedapay_payout_attempts');
    }
};
