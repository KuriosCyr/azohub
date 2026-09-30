<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'billing_period',
        'is_trial',
        'starts_at',
        'ends_at',
        'auto_renew',
        'reminded_at',
        'referral_credit_applied',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renew' => 'boolean',
        'is_trial' => 'boolean',
        'reminded_at' => 'datetime',
        'referral_credit_applied' => 'decimal:2',
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

    // Montant réellement débité au prestataire (prix du plan réduit du crédit de parrainage
    // éventuellement appliqué — voir applyReferralCredit()).
    public function getTotalChargedAttribute(): float
    {
        return round((float) $this->plan->priceFor($this->billing_period) - (float) $this->referral_credit_applied, 2);
    }

    // Consomme le crédit de parrainage du prestataire sur cet abonnement, si demandé et pas déjà
    // fait (idempotent, même principe que Order::applyReferralCredit()). Toujours appelé avant
    // de construire la transaction FedaPay — au moins 1 FCFA reste toujours à payer même si le
    // crédit disponible suffirait à tout couvrir (ex. plafond de 3000 FCFA = un mois de plan Pro
    // pile, mais FedaPay exige un montant strictement positif). Retourne le montant appliqué.
    public function applyReferralCredit(bool $useReferralCredit): float
    {
        if (!$useReferralCredit || (float) $this->referral_credit_applied > 0) {
            return (float) $this->referral_credit_applied;
        }

        $redeemableCap = max(0, (float) $this->total_charged - 1);
        $redeemed = $this->user->redeemReferralCredit($redeemableCap, $this);

        if ($redeemed > 0) {
            $this->update(['referral_credit_applied' => $redeemed]);
            $this->refresh();
        }

        return $redeemed;
    }

    // Activer/renouveler l'abonnement (appelé après confirmation du paiement FedaPay)
    // $carryOverFrom : fin d'un abonnement du même plan encore en cours (renouvellement
    // anticipé) — la nouvelle période s'ajoute à ce qu'il restait au lieu de le perdre.
    public function renew(?\Carbon\Carbon $carryOverFrom = null)
    {
        $base = ($carryOverFrom && $carryOverFrom->isFuture()) ? $carryOverFrom->copy() : now();

        $this->starts_at = now();
        $this->ends_at = $this->billing_period === 'yearly' ? $base->addYear() : $base->addMonth();
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

    // Tentative de paiement abandonnée avant confirmation — page FedaPay jamais terminée,
    // paiement explicitement marqué échoué (Payment::markAsFailed()), ou trop ancienne pour
    // espérer une confirmation tardive (voir la commande planifiée ExpireStalePendingPayments).
    // Restitue le crédit de parrainage éventuellement consommé (Subscription::
    // applyReferralCredit()) : sans ça, il disparaissait pour toujours alors qu'aucun argent n'a
    // jamais été réellement débité pour cette tentative (audit externe).
    public function cancelAbandoned(): void
    {
        DB::transaction(function () {
            // withTrashed() (audit externe — 3e audit) : un abonnement 'pending' supprimé par un
            // admin restait invisible à cette requête (global scope SoftDeletes), donc jamais
            // annulé — le crédit de parrainage consommé dessus restait bloqué pour toujours. La
            // suppression n'empêche pas cette annulation : elle ne fait que libérer le crédit et
            // marquer l'abonnement 'cancelled', rien qui ressuscite un enregistrement supprimé.
            $subscription = static::withTrashed()->whereKey($this->id)->lockForUpdate()->first();

            if (!$subscription || $subscription->status !== 'pending') {
                return;
            }

            if ((float) $subscription->referral_credit_applied > 0) {
                $subscription->user->refundReferralCredit((float) $subscription->referral_credit_applied, $subscription);
            }

            $subscription->update([
                'status' => 'cancelled',
                'referral_credit_applied' => 0,
            ]);
        });

        $this->refresh();
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
