<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Historique en lecture seule des utilisations d'un code promo — sert à la fois de compteur
// (max_uses) et de garde-fou "une fois par utilisateur" (voir PromoCode::hasReachedMaxUses()/
// alreadyUsedBy()). Supprimée seulement si la commande associée est annulée avant tout
// paiement réussi (voir Order::refund()), pour libérer le code — jamais modifiée sinon.
class PromoCodeRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'order_id',
        'amount_applied',
    ];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
