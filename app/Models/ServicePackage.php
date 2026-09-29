<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use HasFactory;

    public const TIERS = [
        'basic' => 'Basique',
        'standard' => 'Standard',
        'premium' => 'Premium',
    ];

    protected $fillable = [
        'service_id',
        'tier',
        'price',
        'delivery_time',
        'revisions_included',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'delivery_time' => 'integer',
        'revisions_included' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getTierLabelAttribute(): string
    {
        return self::TIERS[$this->tier] ?? $this->tier;
    }
}
