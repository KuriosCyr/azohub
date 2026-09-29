<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'expires_at',
        'min_order_amount',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_uses' => 'integer',
        'expires_at' => 'datetime',
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasReachedMaxUses(): bool
    {
        return $this->max_uses !== null && $this->redemptions()->count() >= $this->max_uses;
    }

    public function alreadyUsedBy(int $userId): bool
    {
        return $this->redemptions()->where('user_id', $userId)->exists();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function meetsMinimumOrder(float $totalCharged): bool
    {
        return $this->min_order_amount === null || $totalCharged >= (float) $this->min_order_amount;
    }

    // Réduction que ce code applique à un montant donné, plafonnée à deux choses : au moins
    // 1 FCFA doit rester à payer (FedaPay exige un montant strictement positif, comme le crédit
    // de parrainage — voir Order::applyReferralCredit()), ET la réduction ne peut jamais dépasser
    // la marge d'Azohub sur cette commande (commission + frais client, $marginCap) — audit
    // externe : sans ce deuxième plafond, un gros code (ou un code à 100%) faisait payer plus au
    // prestataire que ce qu'Azohub avait réellement encaissé, une perte nette sur la commande.
    public function discountFor(float $totalCharged, float $marginCap): float
    {
        $rawDiscount = $this->type === 'percentage'
            ? round($totalCharged * ((float) $this->value / 100), 2)
            : (float) $this->value;

        $cap = min(max(0, $totalCharged - 1), max(0, $marginCap));

        return round(min($rawDiscount, $cap), 2);
    }
}
