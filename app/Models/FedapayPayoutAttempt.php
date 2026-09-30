<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Historique permanent de chaque identifiant de virement FedaPay jamais utilisé pour une demande
// de retrait — jamais effacé, même quand WithdrawalRequest::clearAmbiguousFedapayAttempt() vide
// fedapay_payout_id sur la demande elle-même. Permet à PaymentService::processPayoutUpdate() de
// retrouver (et d'alerter sur) un webhook concernant un ancien identifiant qui n'est plus
// "l'actuel" pour sa demande.
class FedapayPayoutAttempt extends Model
{
    protected $fillable = [
        'withdrawal_request_id',
        'fedapay_payout_id',
    ];

    public function withdrawalRequest()
    {
        return $this->belongsTo(WithdrawalRequest::class);
    }
}
