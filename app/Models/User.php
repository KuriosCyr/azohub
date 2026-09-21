<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, MustVerifyEmailTrait;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->slug)) {
                $user->slug = static::generateUniqueSlug($user->name);
            }
        });
    }

    // Génère un slug unique (ex: "yves-adjovi", puis "yves-adjovi-2" en cas de collision).
    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'utilisateur';
        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    protected $fillable = [
        'name',
        'slug',
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

    // "En ligne" : vu il y a moins de 5 minutes (mis à jour par UpdateLastSeen sur
    // chaque requête authentifiée). Pas de temps réel, juste un indicateur approximatif.
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->gt(now()->subMinutes(5));
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

    // Relations pour les clients
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }

    // Orders en tant que prestataire
    public function prestataireOrders()
    {
        return $this->hasMany(Order::class, 'prestataire_id');
    }

    public function conversationsAsPrestataire()
    {
        return $this->hasMany(Conversation::class, 'prestataire_id');
    }

    // Délai moyen (en minutes) avant la première réponse du prestataire à un premier
    // message client, calculé sur ses 30 conversations les plus récentes. Null tant
    // qu'il n'a pas encore répondu à un premier contact.
    public function averageResponseTimeMinutes(): ?int
    {
        $conversations = $this->conversationsAsPrestataire()
            ->with(['messages' => fn ($q) => $q->orderBy('created_at')])
            ->latest('created_at')
            ->take(30)
            ->get();

        $deltas = [];

        foreach ($conversations as $conversation) {
            $firstClientMessage = $conversation->messages->firstWhere('sender_id', $conversation->client_id);

            if (!$firstClientMessage) {
                continue;
            }

            $firstReply = $conversation->messages
                ->where('sender_id', $this->id)
                ->where('created_at', '>', $firstClientMessage->created_at)
                ->first();

            if ($firstReply) {
                $deltas[] = $firstClientMessage->created_at->diffInMinutes($firstReply->created_at);
            }
        }

        if (empty($deltas)) {
            return null;
        }

        return (int) round(array_sum($deltas) / count($deltas));
    }

    public function responseTimeLabel(): string
    {
        $minutes = $this->averageResponseTimeMinutes();

        if ($minutes === null) {
            return 'Pas encore de données';
        }
        if ($minutes < 60) {
            return '< 1h';
        }

        $hours = (int) round($minutes / 60);
        if ($hours < 24) {
            return $hours . 'h';
        }

        return (int) round($hours / 24) . ' j';
    }

    // Taux de commandes menées à terme parmi celles qui ont abouti (terminée ou annulée) ;
    // les commandes encore en cours ne comptent ni pour ni contre. Null si aucune donnée.
    public function completionRate(): ?float
    {
        $concluded = $this->prestataireOrders()->whereIn('status', ['completed', 'cancelled'])->count();

        if ($concluded === 0) {
            return null;
        }

        $completed = $this->prestataireOrders()->where('status', 'completed')->count();

        return round(($completed / $concluded) * 100);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'prestataire_id');
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

        $this->awardMilestoneBadges($completedOrders, $rating);
    }

    // Badges d'étape gagnés automatiquement selon les commandes réalisées et la note
    // moyenne. Cumulatifs : jamais retirés une fois obtenus, même si la note baisse
    // ensuite (ce sont des jalons atteints, pas un statut courant).
    private function awardMilestoneBadges(int $completedOrders, float $rating): void
    {
        $orderMilestones = [
            10 => '10 commandes',
            50 => '50 commandes',
            100 => '100 commandes',
        ];

        foreach ($orderMilestones as $threshold => $badge) {
            if ($completedOrders >= $threshold) {
                $this->addBadge($badge);
            }
        }

        if ($completedOrders >= 20 && $rating >= 4.8) {
            $this->addBadge('Top Rated');
        }
    }

    // Fin de l'offre de bienvenue (commission réduite les premiers mois), ou null si elle
    // est désactivée, terminée, ou si ce n'est pas un prestataire.
    public function welcomePromoEndsAt(): ?\Carbon\Carbon
    {
        $rate = (float) config('services.azohub.welcome_promo.rate');
        $days = (int) config('services.azohub.welcome_promo.days');

        if (!$this->isPrestataire() || $rate <= 0 || $days <= 0 || !$this->created_at) {
            return null;
        }

        $endsAt = $this->created_at->copy()->addDays($days);

        return $endsAt->isFuture() ? $endsAt : null;
    }

    // Libellé affichable du niveau (avec l'accent : ucfirst('confirme') donnait « Confirme »).
    public function getLevelLabelAttribute(): string
    {
        return ['nouveau' => 'Nouveau', 'confirme' => 'Confirmé', 'expert' => 'Expert'][$this->level] ?? ucfirst((string) $this->level);
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

        $rates = [$levelRate, $planRate];

        if ($this->welcomePromoEndsAt()) {
            $rates[] = ((float) config('services.azohub.welcome_promo.rate')) / 100;
        }

        return min($rates);
    }

    // Nombre de services publiables gratuitement selon le niveau (gagné par la
    // performance, indépendant de tout abonnement).
    private const LEVEL_SERVICE_ALLOWANCE = [
        'nouveau' => 6,
        'confirme' => 10,
        'expert' => 15,
    ];

    // Limite de services : le plus avantageux entre l'allocation gratuite du niveau
    // et la limite de l'abonnement payant. Un plan à null (illimité) l'emporte toujours,
    // mais l'ABSENCE de plan (ex: plan Gratuit supprimé par erreur) ne doit jamais être
    // confondue avec "illimité" : on retombe alors sur la seule allocation du niveau.
    public function maxServices(): ?int
    {
        $levelAllowance = self::LEVEL_SERVICE_ALLOWANCE[$this->level] ?? self::LEVEL_SERVICE_ALLOWANCE['nouveau'];
        $plan = $this->currentPlan();

        if ($plan === null) {
            return $levelAllowance;
        }

        if ($plan->max_services === null) {
            return null;
        }

        return max($levelAllowance, $plan->max_services);
    }

    // Places de services occupées : les services refusés n'en occupent pas (le prestataire peut
    // en créer un autre) ; en attente de modération, validés ou désactivés, ils comptent.
    public function serviceSlotsUsed(): int
    {
        return $this->services()->where('status', '!=', 'rejected')->count();
    }

    public function hasFreeServiceSlot(): bool
    {
        $max = $this->maxServices();

        return $max === null || $this->serviceSlotsUsed() < $max;
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

    // Créditer le portefeuille (increment() = requête atomique côté DB, pas de
    // perte d'écriture si deux crédits arrivent en même temps sur le même compte).
    public function creditWallet(float $amount)
    {
        $this->increment('wallet_balance', $amount);
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
