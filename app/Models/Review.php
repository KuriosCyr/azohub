<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Un avis masqué/réaffiché par l'admin doit immédiatement changer la note
        // moyenne affichée sur le profil et le service concernés.
        static::updated(function (Review $review) {
            if ($review->wasChanged('is_visible')) {
                $review->reviewee?->updateRating();
                if ($review->review_type === 'client_to_prestataire') {
                    $review->service?->updateRating();
                }
            }
        });
    }

    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewee_id',
        'service_id',
        'rating',
        'comment',
        'quality_rating',
        'communication_rating',
        'timeliness_rating',
        'clarity_rating',
        'responsiveness_rating',
        'review_type',
        'is_visible',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'quality_rating' => 'integer',
        'communication_rating' => 'integer',
        'timeliness_rating' => 'integer',
        'clarity_rating' => 'integer',
        'responsiveness_rating' => 'integer',
        'is_visible' => 'boolean',
        'responded_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Accesseurs pour compatibilité
    public function getClientIdAttribute()
    {
        // Si c'est un avis client vers prestataire
        return $this->review_type === 'client_to_prestataire' ? $this->reviewer_id : $this->reviewee_id;
    }

    public function getPrestataireIdAttribute()
    {
        // Si c'est un avis client vers prestataire
        return $this->review_type === 'client_to_prestataire' ? $this->reviewee_id : $this->reviewer_id;
    }

    // Méthode pour obtenir la note moyenne des critères
    public function getAverageDetailedRating()
    {
        if ($this->review_type === 'client_to_prestataire') {
            return round(($this->quality_rating + $this->communication_rating + $this->timeliness_rating) / 3, 2);
        } else {
            return round(($this->clarity_rating + $this->responsiveness_rating) / 2, 2);
        }
    }

    // Scope
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    // Réponse du prestataire à un avis client -> prestataire : facultative, une seule fois,
    // jamais modifiable ensuite. L'autorisation (qui peut répondre, une seule fois) est
    // vérifiée par l'appelant (Livewire\ReviewResponse) ; ce modèle ne fait qu'écrire.
    public function respond(string $text): void
    {
        $this->update(['response' => $text, 'responded_at' => now()]);
    }

}