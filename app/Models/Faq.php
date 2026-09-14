<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'asc');
    }

    // Categories disponibles
    public static function categories()
    {
        return [
            'general' => 'Questions générales',
            'prestataire' => 'Pour les prestataires',
            'client' => 'Pour les clients',
            'paiement' => 'Paiements & Facturation',
            'securite' => 'Sécurité & Confidentialité',
        ];
    }

    // Helpers
    public function getCategoryLabelAttribute()
    {
        return self::categories()[$this->category] ?? $this->category;
    }
}