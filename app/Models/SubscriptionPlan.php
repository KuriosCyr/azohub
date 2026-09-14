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

    // Scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
