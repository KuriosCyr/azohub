<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Historique en lecture seule des mouvements de crédit de parrainage (voir User::
// maybeRewardReferrer()/redeemReferralCredit()/refundReferralCredit(), seuls points
// d'écriture) — jamais modifié ni supprimé après coup. Même principe que WalletTransaction,
// pour users.referral_credit_balance au lieu de wallet_balance.
class ReferralCreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'reason',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
