<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Corrige un point signalé par un 2e audit externe : contrairement aux commandes, les abonnements
// étaient supprimables DÉFINITIVEMENT (individuellement ou en masse) dans l'admin — ce qui
// effaçait en cascade leurs paiements (payments.subscription_id en onDelete('cascade')).
class SubscriptionSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_subscription_is_soft_and_reversible(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 3000,
            'status' => 'success',
            'type' => 'subscription',
        ]);

        $subscription->delete();

        // Disparaît des requêtes normales...
        $this->assertNull(Subscription::find($subscription->id));
        // ...mais existe toujours en base, tout comme son paiement (pas de cascade déclenchée
        // par une suppression douce).
        $this->assertNotNull(Subscription::withTrashed()->find($subscription->id));
        $this->assertNotNull(Payment::find($payment->id));

        $subscription->restore();

        $this->assertNotNull(Subscription::find($subscription->id));
    }
}
