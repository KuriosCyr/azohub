<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'billing_period',
        'max_services',
        'commission_rate',
        'features',
        'is_popular',
        'is_active',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'features' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Relations
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Prix à payer pour une période donnée (12 mois : yearly_price, sinon 12 x le mensuel).
    public function priceFor(string $period): float
    {
        if ($period === 'yearly') {
            return (float) ($this->yearly_price ?? ((float) $this->price * 12));
        }

        return (float) $this->price;
    }

    public function hasYearlyOffer(): bool
    {
        return (float) $this->price > 0 && $this->yearly_price !== null;
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
