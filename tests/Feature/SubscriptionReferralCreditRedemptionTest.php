<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Un prestataire ne peut jamais dépenser son crédit de parrainage comme un client (les parcours
// de commande sont réservés aux comptes 'client', cf. middleware EnsureUserIsClient) : ce crédit
// s'applique donc en réduction sur son propre abonnement Pro/Premium — même principe que
// Order::applyReferralCredit(), jamais l'appel réel à FedaPay.
class SubscriptionReferralCreditRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function makePlan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'yearly_price' => 30000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ], $overrides));
    }

    private function makeSubscription(User $prestataire, SubscriptionPlan $plan, array $overrides = []): Subscription
    {
        return Subscription::create(array_merge([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ], $overrides));
    }

    public function test_a_full_referral_credit_covers_a_pro_plan_but_leaves_1_fcfa(): void
    {
        // 30 filleuls récompensés = 3000 FCFA, exactement le prix du plan Pro.
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 3000]);
        $plan = $this->makePlan();
        $subscription = $this->makeSubscription($prestataire, $plan);

        $applied = $subscription->applyReferralCredit(true);

        $this->assertEquals(2999, $applied);
        $this->assertEquals(1, $subscription->fresh()->total_charged);
        $this->assertEquals(1, $prestataire->fresh()->referral_credit_balance);
    }

    public function test_a_partial_credit_only_covers_what_is_available(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
        $plan = $this->makePlan();
        $subscription = $this->makeSubscription($prestataire, $plan);

        $applied = $subscription->applyReferralCredit(true);

        $this->assertEquals(500, $applied);
        $this->assertEquals(2500, $subscription->fresh()->total_charged);
        $this->assertEquals(0, $prestataire->fresh()->referral_credit_balance);
    }

    public function test_applying_credit_is_idempotent_on_a_retried_subscription(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
        $plan = $this->makePlan();
        $subscription = $this->makeSubscription($prestataire, $plan);

        $subscription->applyReferralCredit(true);
        $subscription->applyReferralCredit(true);

        $this->assertEquals(500, $subscription->fresh()->referral_credit_applied);
        $this->assertEquals(0, $prestataire->fresh()->referral_credit_balance);
    }

    public function test_not_requesting_credit_applies_nothing(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
        $plan = $this->makePlan();
        $subscription = $this->makeSubscription($prestataire, $plan);

        $applied = $subscription->applyReferralCredit(false);

        $this->assertEquals(0, $applied);
        $this->assertEquals(500, $prestataire->fresh()->referral_credit_balance);
    }

    public function test_credit_applies_the_same_way_on_a_yearly_plan(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 3000]);
        $plan = $this->makePlan();
        $subscription = $this->makeSubscription($prestataire, $plan, ['billing_period' => 'yearly']);

        $applied = $subscription->applyReferralCredit(true);

        $this->assertEquals(3000, $applied);
        $this->assertEquals(30000 - 3000, $subscription->fresh()->total_charged);
    }
}
