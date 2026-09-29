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
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_uses' => 'integer',
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

    // Réduction que ce code applique à un montant donné — toujours plafonnée pour laisser au
    // moins 1 FCFA à payer (FedaPay exige un montant strictement positif, comme le crédit de
    // parrainage — voir Order::applyReferralCredit()).
    public function discountFor(float $totalCharged): float
    {
        $rawDiscount = $this->type === 'percentage'
            ? round($totalCharged * ((float) $this->value / 100), 2)
            : (float) $this->value;

        return round(min($rawDiscount, max(0, $totalCharged - 1)), 2);
    }
}
