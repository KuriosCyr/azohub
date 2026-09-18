<?php

namespace App\Livewire;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PrestataireSubscription extends Component
{
    public ?int $selectedPlanId = null;
    public string $paymentMethod = 'mtn_momo';

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

    public function selectPlan(int $planId)
    {
        $this->selectedPlanId = $planId;
    }

    public function choosePlan(int $planId, PaymentService $payments)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::active()->findOrFail($planId);

        if ($this->currentPlan && $this->currentPlan->id === $plan->id) {
            session()->flash('error', 'Vous êtes déjà sur ce plan.');
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

        $this->validate([
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
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
