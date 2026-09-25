<?php

namespace App\Models;

use App\Notifications\ServiceApproved;
use App\Notifications\ServiceRejected;
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
        'service_areas',
        'serves_nationwide',
        'cover_image',
        'tags',
        'rating',
        'total_orders',
        'total_reviews',
        'orders_count',
        'status',
        'moderation_note',
        'reviewed_at',
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
        'serves_nationwide' => 'boolean',
        'service_areas' => 'array',
        'reviewed_at' => 'datetime',
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

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
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

    // Liste à plat des 77 communes du Bénin (config/communes.php), triée : sert de référence pour
    // valider et afficher les zones d'intervention.
    public static function communes(): array
    {
        $all = collect(config('communes', []))->flatten()->unique()->values()->all();
        sort($all);

        return $all;
    }

    // Communes desservies (vide si « tout le Bénin »). Un ancien service sans zone se limite à sa ville.
    public function areasList(): array
    {
        if ($this->serves_nationwide) {
            return [];
        }

        if (!empty($this->service_areas)) {
            return array_values($this->service_areas);
        }

        $city = $this->city ?: $this->prestataire?->city;

        return $city ? [$city] : [];
    }

    // Libellé court pour les cartes : « Cotonou, Ouidah +2 » ou « Partout au Bénin ».
    public function areasLabel(): string
    {
        if ($this->serves_nationwide) {
            return 'Partout au Bénin';
        }

        $areas = $this->areasList();

        if (!$areas) {
            return 'Bénin';
        }

        $extra = count($areas) - 2;

        return implode(', ', array_slice($areas, 0, 2)) . ($extra > 0 ? ' +' . $extra : '');
    }

    // Les tags sont recherchés par LIKE : on stocke le JSON sans échapper les accents (\u00e9),
    // sinon une recherche « électricité » ne trouverait jamais le tag « électricité ».
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    // Modération : un service (nouveau ou modifié) n'est public qu'une fois validé par l'équipe.
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'pending' => 'En modération',
        'active' => 'Validé',
        'paused' => 'En pause',
        'rejected' => 'Refusé',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) $this->status;
    }

    public function approve(): void
    {
        $this->update(['status' => 'active', 'moderation_note' => null, 'reviewed_at' => now()]);
        $this->prestataire?->notify(new ServiceApproved($this));
    }

    public function reject(string $reason): void
    {
        $this->update(['status' => 'rejected', 'moderation_note' => $reason, 'reviewed_at' => now()]);
        $this->prestataire?->notify(new ServiceRejected($this));
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
