<?php

namespace App\Livewire;

use App\Models\Subscription;
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

    public function getTrialEligibleProperty(): bool
    {
        return Auth::user()->isTrialEligible();
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

    public function selectPlan(int $planId)
    {
        $this->selectedPlanId = $planId;
    }

    public function toggleAutoRenew()
    {
        if (!$this->activeSubscription) {
            return;
        }

        $this->activeSubscription->toggleAutoRenew();
    }

    // Politique (celle des plateformes d'abonnement sérieuses) :
    // - Monter en gamme (Pro -> Premium) : immédiat, et le temps déjà payé sur l'ancien plan est
    //   reporté sur le nouveau (aucune perte).
    // - Descendre en gamme (Premium -> Pro, ou vers Gratuit) : jamais immédiat. Le plan actuel,
    //   déjà payé, reste actif jusqu'à sa date de fin normale ; le nouveau choix ne prend effet
    //   qu'à ce moment-là. Personne ne perd ce qu'il a payé.
    public function choosePlan(int $planId, PaymentService $payments)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::active()->findOrFail($planId);
        $active = $this->activeSubscription;

        if ($this->currentPlan && $this->currentPlan->id === $plan->id && !$this->canRenew) {
            session()->flash('error', 'Vous êtes déjà sur ce plan.');
            return;
        }

        $isDowngrade = $active
            && $active->subscription_plan_id !== $plan->id
            && (float) $active->plan->price > 0
            && $active->ends_at->isFuture()
            && (float) $plan->price < (float) $active->plan->price;

        if ($isDowngrade) {
            session()->flash('info', "Vous restez sur le plan {$active->plan->name} (déjà payé) jusqu'au {$active->ends_at->translatedFormat('d M Y')}. Revenez ici après cette date pour passer sur {$plan->name}.");
            $this->selectedPlanId = null;
            return;
        }

        // Choix du plan Gratuit : si un plan payant est encore actif, on ne bascule pas tout de
        // suite dessus (ce serait perdre ce qui a déjà été payé) — il continue de s'appliquer
        // jusqu'à sa fin, le plan Gratuit prend le relais automatiquement ensuite.
        if ((float) $plan->price <= 0) {
            if ($active) {
                session()->flash('info', "Vous restez sur le plan {$active->plan->name} (déjà payé) jusqu'au {$active->ends_at->translatedFormat('d M Y')}, puis vous basculerez automatiquement sur le plan Gratuit.");
                $this->selectedPlanId = null;
                return;
            }

            session()->flash('success', 'Vous êtes sur le plan Gratuit.');
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
                    'auto_renew' => true,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                ]);

                $user->notify(new SubscriptionActivated($trial));
            });

            // Le modèle Auth::user() garde en cache la relation activeSubscription telle qu'elle
            // était AVANT cette création (elle a été lue plus haut, via $this->currentPlan) :
            // sans ça, currentPlan()/activeSubscription restent figés sur "Gratuit" jusqu'au
            // prochain chargement de page complet, alors que l'abonnement est bien créé en base.
            $user->unsetRelation('activeSubscription');

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
            'auto_renew' => true,
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
