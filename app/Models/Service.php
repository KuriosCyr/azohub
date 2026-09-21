<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'what_included',
        'price',
        'price_type',
        'delivery_time',
        'city',
        'service_area',
        'cover_image',
        'tags',
        'rating',
        'total_orders',
        'total_reviews',
        'orders_count',
        'status',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'delivery_time' => 'integer',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'total_orders' => 'integer',
        'orders_count' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    // Relations
    public function prestataire()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        // Un service est actif si status='active' ET is_active=true.
        // Colonnes qualifiées : les appelants peuvent joindre d'autres tables
        // (ex: users/subscriptions) qui ont elles aussi une colonne "status".
        // Le prestataire ne doit pas être désactivé/banni (ni supprimé) : ses services disparaissent
        // avec lui des listes publiques.
        return $query->where('services.status', 'active')
            ->where('services.is_active', true)
            ->whereHas('prestataire', fn ($q) => $q->where(fn ($q) => $q->where('is_active', true)->orWhereNull('is_active')));
    }

    // Peut-on encore consulter publiquement / commander ce service ? (status ET is_active
    // ET prestataire actif — is_active seul laissait passer un service rejeté par l'admin.)
    public function isOrderable(): bool
    {
        return $this->status === 'active'
            && $this->is_active
            && $this->prestataire !== null
            && $this->prestataire->is_active !== false;
    }

    // Methods
    /**
     * Mettre à jour la note moyenne du service
     */
    public function updateRating()
    {
        $reviews = \App\Models\Review::where('service_id', $this->id)
            ->where('review_type', 'client_to_prestataire')
            ->visible()
            ->get();

        if ($reviews->isEmpty()) {
            $this->update(['rating' => 0, 'total_reviews' => 0]);
            return;
        }

        // Calculer la moyenne
        $avgRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        $this->update([
            'rating' => round($avgRating, 2),
            'total_reviews' => $totalReviews,
        ]);
    }

    public function incrementOrders()
    {
        $this->increment('total_orders');
        $this->increment('orders_count');
    }
}
