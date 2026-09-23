<?php

namespace App\Livewire;

use App\Models\Subscription;
use App\Models\Payment;
use App\Notifications\SubscriptionActivated;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PrestataireSubscription extends Component
{
    public ?int $selectedPlanId = null;
    public string $paymentMethod = 'mtn_momo';
    public string $billingPeriod = 'monthly'; // monthly | yearly

    // Confirmation avant de changer vers un AUTRE plan payant alors qu'il reste du temps payé
    // sur l'abonnement en cours (voir Payment::markAsPaid : ce reliquat n'est reporté que pour
    // un renouvellement du même plan, sinon il est simplement perdu).
    public bool $confirmingSwitch = false;
    public ?int $switchWarningPlanId = null;
    public int $switchWarningDaysLost = 0;

    public function mount()
    {
        // Lien de renouvellement rapide envoyé par le rappel d'expiration
        // (?renew=<plan_id>) : présélectionne directement le plan concerné.
        $renewPlanId = request()->query('renew');
        if ($renewPlanId && SubscriptionPlan::active()->whereKey($renewPlanId)->exists()) {
            $this->selectedPlanId = (int) $renewPlanId;
        }
    }

    public function getPlansProperty()
    {
        return SubscriptionPlan::active()->get();
    }

    public function getCurrentPlanProperty()
    {
        return Auth::user()->currentPlan();
    }

    public function getActiveSubscriptionProperty()
    {
        return Auth::user()->activeSubscription;
    }

    // Premier mois offert : une seule fois par prestataire, sur un plan payant mensuel, tant
    // qu'il n'a jamais eu d'essai ni payé d'abonnement.
    public function getTrialEligibleProperty(): bool
    {
        $userId = Auth::id();

        return !Subscription::where('user_id', $userId)->where('is_trial', true)->exists()
            && !Payment::where('user_id', $userId)->where('type', 'subscription')->where('status', 'success')->exists();
    }

    public function setBillingPeriod(string $period)
    {
        $this->billingPeriod = $period === 'yearly' ? 'yearly' : 'monthly';
    }

    // Renouvellement anticipé : autorisé pour le plan payant en cours dans les 7 derniers jours
    // (avant, le plan courant n'offrait aucun bouton et il fallait le laisser expirer).
    public function getCanRenewProperty(): bool
    {
        $subscription = $this->activeSubscription;

        return $subscription
            && (float) $subscription->plan->price > 0
            && $subscription->ends_at->lte(now()->addDays(7));
    }

    public function getSwitchWarningPlanProperty(): ?SubscriptionPlan
    {
        return $this->switchWarningPlanId
            ? SubscriptionPlan::find($this->switchWarningPlanId)
            : null;
    }

    public function selectPlan(int $planId)
    {
        $this->selectedPlanId = $planId;
    }

    public function cancelSwitchWarning()
    {
        $this->confirmingSwitch = false;
        $this->switchWarningPlanId = null;
        $this->switchWarningDaysLost = 0;
    }

    public function confirmPlanSwitch(PaymentService $payments)
    {
        $planId = $this->switchWarningPlanId;
        $this->confirmingSwitch = false;
        $this->switchWarningPlanId = null;
        $this->switchWarningDaysLost = 0;

        if ($planId) {
            $this->choosePlan($planId, $payments, skipSwitchWarning: true);
        }
    }

    public function toggleAutoRenew()
    {
        if (!$this->activeSubscription) {
            return;
        }

        $this->activeSubscription->toggleAutoRenew();
    }

    public function choosePlan(int $planId, PaymentService $payments, bool $skipSwitchWarning = false)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::active()->findOrFail($planId);

        if ($this->currentPlan && $this->currentPlan->id === $plan->id && !$this->canRenew) {
            session()->flash('error', 'Vous êtes déjà sur ce plan.');
            return;
        }

        // Changement vers un AUTRE plan payant alors qu'il reste du temps payé sur l'abonnement
        // en cours : ce reliquat serait perdu (Payment::markAsPaid ne le reporte que pour un
        // renouvellement du même plan). On demande confirmation plutôt que de le perdre en silence.
        if (
            !$skipSwitchWarning
            && $this->activeSubscription
            && $this->activeSubscription->subscription_plan_id !== $planId
            && (float) $this->activeSubscription->plan->price > 0
            && $this->activeSubscription->ends_at->isFuture()
        ) {
            $this->switchWarningPlanId = $planId;
            $this->switchWarningDaysLost = (int) ceil(now()->diffInHours($this->activeSubscription->ends_at) / 24);
            $this->confirmingSwitch = true;
            return;
        }

        // Plan gratuit : pas de paiement, on annule simplement l'abonnement payant en cours.
        if ((float) $plan->price <= 0) {
            if ($this->activeSubscription) {
                $this->activeSubscription->cancel();
            }

            session()->flash('success', 'Vous êtes maintenant sur le plan Gratuit.');
            return;
        }

        $period = $plan->hasYearlyOffer() ? $this->billingPeriod : 'monthly';

        // Mois d'essai offert : activation immédiate, sans paiement.
        if ($period === 'monthly' && $this->trialEligible) {
            DB::transaction(function () use ($user, $plan) {
                Subscription::where('user_id', $user->id)->where('status', 'active')->update(['status' => 'cancelled']);

                $trial = Subscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_period' => 'monthly',
                    'is_trial' => true,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                ]);

                $user->notify(new SubscriptionActivated($trial));
            });

            $this->selectedPlanId = null;
            session()->flash('success', "Votre mois offert du plan {$plan->name} est activé. Profitez-en !");
            return;
        }

        $this->validate([
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => $period,
        ]);

        try {
            $url = $payments->initiateForSubscription($subscription, $this->paymentMethod);
        } catch (\Throwable $e) {
            report($e);
            $subscription->update(['status' => 'cancelled']);

            session()->flash('error', 'Le paiement n\'a pas pu être initié. Réessayez.');
            return;
        }

        return redirect()->away($url);
    }

    public function render()
    {
        return view('livewire.prestataire-subscription')
            ->layout('components.layouts.app');
    }
}
