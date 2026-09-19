<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'auto_renew',
        'reminded_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renew' => 'boolean',
        'reminded_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    // Vérifier si l'abonnement est actif
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->starts_at <= now() 
            && $this->ends_at >= now();
    }

    // Activer/renouveler l'abonnement (appelé après confirmation du paiement FedaPay)
    public function renew()
    {
        $this->starts_at = now();
        $this->ends_at = $this->plan->billing_period === 'yearly' ? now()->addYear() : now()->addMonth();
        $this->status = 'active';
        $this->reminded_at = null;
        $this->save();
    }

    // Annuler l'abonnement
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->auto_renew = false;
        $this->save();
    }

    // "Renouvellement automatique" : FedaPay ne permet pas de prélèvement silencieux
    // (pas de carte enregistrée), donc ça ne prélève jamais seul. Ça personnalise
    // juste le rappel d'expiration avec un lien de renouvellement rapide pré-rempli.
    public function toggleAutoRenew(): void
    {
        $this->update(['auto_renew' => !$this->auto_renew]);
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
