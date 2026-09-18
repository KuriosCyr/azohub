<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'advertiser_name',
        'advertiser_contact',
        'image',
        'link_url',
        'placement',
        'order',
        'starts_at',
        'ends_at',
        'is_active',
        'impressions',
        'clicks',
        'price_paid',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'price_paid' => 'decimal:2',
    ];

    public static function placements(): array
    {
        return [
            'home_banner' => 'Bannière accueil',
            'services_sidebar' => 'Barre latérale — liste des services',
        ];
    }

    public function getPlacementLabelAttribute(): string
    {
        return self::placements()[$this->placement] ?? $this->placement;
    }

    // Scopes
    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where('starts_at', '<=', $today)
            ->where('ends_at', '>=', $today);
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }

    // Helpers
    public function recordImpression(): void
    {
        $this->increment('impressions');
    }

    public function recordClick(): void
    {
        $this->increment('clicks');
    }
}
