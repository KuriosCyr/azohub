<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, MustVerifyEmailTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'bio',
        'city',
        'service_areas',
        'languages',
        'availability',
        'rating',
        'total_reviews',
        'completed_orders',
        'level',
        'badges',
        'identity_verified',
        'identity_document',
        'identity_verification_status',
        'identity_rejection_reason',
        'wallet_balance',
        'is_active',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'service_areas' => 'array',
        'languages' => 'array',
        'badges' => 'array',
        'rating' => 'decimal:2',
        'wallet_balance' => 'decimal:2',
        'identity_verified' => 'boolean',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    // Filament Admin Access
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    // Vérifier si l'utilisateur est un prestataire
    public function isPrestataire(): bool
    {
        return $this->role === 'prestataire';
    }

    // Vérifier si l'utilisateur est un client
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // Relations pour les services
    public function services()
    {
        // CORRECTION: user_id au lieu de prestataire_id
        return $this->hasMany(Service::class, 'user_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('ends_at');
    }

    // Plan courant : celui de l'abonnement payant actif, sinon le plan Gratuit par défaut.
    public function currentPlan(): ?SubscriptionPlan
    {
        return $this->activeSubscription?->plan ?? SubscriptionPlan::where('slug', 'gratuit')->first();
    }

    public function receivedProposals()
    {
        return $this->hasManyThrough(Proposal::class, ServiceRequest::class, 'client_id', 'service_request_id');
    }

    public function sentProposals()
    {
        // CORRECTION: user_id au lieu de prestataire_id
        return $this->hasMany(Proposal::class, 'user_id');
    }

    // Relations pour les clients
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }

    // Orders en tant que client
    public function clientOrders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    // Orders en tant que prestataire
    public function prestataireOrders()
    {
        return $this->hasMany(Order::class, 'prestataire_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'prestataire_id');
    }

    // Messages envoyés
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Avis donnés
    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    // Avis reçus
    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // Paiements effectués (client)
    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    // Litiges ouverts
    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'opened_by');
    }

    /**
     * Mettre à jour la note moyenne du prestataire
     */
    public function updateRating()
    {
        if (!$this->isPrestataire()) {
            return;
        }

        $reviews = $this->receivedReviews()
            ->where('review_type', 'client_to_prestataire')
            ->get();

        if ($reviews->isEmpty()) {
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

    /**
     * Mettre à jour le niveau du prestataire en fonction de ses stats
     */
    public function updateLevel()
    {
        if (!$this->isPrestataire()) {
            return;
        }

        $completedOrders = $this->completed_orders ?? 0;
        $rating = $this->rating ?? 0;

        // Déterminer le niveau (valeurs alignées sur l'enum users.level : nouveau/confirme/expert)
        if ($completedOrders >= 50 && $rating >= 4.7) {
            $level = 'expert';
        } elseif ($completedOrders >= 15 && $rating >= 4.3) {
            $level = 'confirme';
        } else {
            $level = 'nouveau';
        }

        if ($this->level !== $level) {
            $this->update(['level' => $level]);
        }
    }

    // Taux de commission Azohub : le plus avantageux entre le niveau (gratuit, gagné
    // par la performance) et l'abonnement payant en cours. Aucun des deux systèmes
    // n'écrase l'autre — un expert sur le plan gratuit garde son taux, un nouveau
    // prestataire abonné à un plan payant en profite immédiatement.
    public function commissionRate(): float
    {
        $levelRate = match ($this->level) {
            'expert' => 0.08,
            'confirme' => 0.12,
            default => 0.15,
        };

        $plan = $this->currentPlan();
        $planRate = $plan ? ((float) $plan->commission_rate) / 100 : $levelRate;

        return min($levelRate, $planRate);
    }

    // Anonymise puis supprime (soft delete) le compte. On ne fait pas de suppression
    // définitive : orders.client_id/prestataire_id sont en cascade, une vraie
    // suppression casserait l'historique de commandes de l'autre partie.
    public function anonymizeAndDelete(): void
    {
        if ($this->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->avatar);
        }

        if ($this->identity_document) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($this->identity_document);
        }

        $this->forceFill([
            'name' => 'Utilisateur supprimé',
            'email' => 'compte-supprime-' . $this->id . '-' . now()->timestamp . '@azohub.invalid',
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(40)),
            'phone' => null,
            'avatar' => null,
            'bio' => null,
            'city' => null,
            'service_areas' => null,
            'languages' => null,
            'identity_document' => null,
            'identity_verification_status' => 'none',
            'identity_verified' => false,
            'is_active' => false,
            'remember_token' => null,
        ])->save();

        $this->delete();
    }

    // Soumettre une pièce d'identité pour vérification (remplace un éventuel
    // document précédent — refusé ou non).
    public function submitIdentityDocument(string $path): void
    {
        if ($this->identity_document) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($this->identity_document);
        }

        $this->update([
            'identity_document' => $path,
            'identity_verification_status' => 'pending',
            'identity_verified' => false,
            'identity_rejection_reason' => null,
        ]);
    }

    public function approveIdentityVerification(): void
    {
        $this->update([
            'identity_verification_status' => 'verified',
            'identity_verified' => true,
            'identity_rejection_reason' => null,
        ]);
    }

    public function rejectIdentityVerification(string $reason): void
    {
        $this->update([
            'identity_verification_status' => 'rejected',
            'identity_verified' => false,
            'identity_rejection_reason' => $reason,
        ]);
    }

    // Ajouter un badge
    public function addBadge(string $badge)
    {
        $badges = $this->badges ?? [];
        if (!in_array($badge, $badges)) {
            $badges[] = $badge;
            $this->badges = $badges;
            $this->save();
        }
    }

    // Créditer le portefeuille
    public function creditWallet(float $amount)
    {
        $this->wallet_balance += $amount;
        $this->save();
    }

    // Débiter le portefeuille
    public function debitWallet(float $amount)
    {
        if ($this->wallet_balance >= $amount) {
            $this->wallet_balance -= $amount;
            $this->save();
            return true;
        }
        return false;
    }
}
