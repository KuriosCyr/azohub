<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Corrige un manque identifié par un audit externe : ni les commandes jamais payées, ni les
// tentatives d'abonnement jamais confirmées, n'expiraient automatiquement — le crédit de
// parrainage consommé dessus restait perdu pour toujours (aucun argent n'ayant jamais été
// réellement débité pour ces tentatives).
class ExpireStalePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaleOrder(User $client, array $overrides = []): Order
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create(array_merge([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'client_fee' => 500,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ], $overrides));

        $order->created_at = now()->subHours(25);
        $order->save();

        return $order;
    }

    private function makeStaleSubscription(User $prestataire, array $overrides = []): Subscription
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create(array_merge([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ], $overrides));

        $subscription->created_at = now()->subHours(25);
        $subscription->save();

        return $subscription;
    }

    public function test_a_stale_unpaid_order_is_cancelled_and_restores_its_referral_credit(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeStaleOrder($client);
        $order->applyReferralCredit(true);

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertEquals(300, $client->fresh()->referral_credit_balance);
    }

    public function test_a_recent_unpaid_order_is_left_untouched(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeStaleOrder($client);
        $order->created_at = now()->subHours(1);
        $order->save();

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('pending_payment', $order->fresh()->status);
    }

    public function test_a_stale_pending_subscription_is_cancelled_and_restores_its_referral_credit(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
        $subscription = $this->makeStaleSubscription($prestataire);
        $subscription->applyReferralCredit(true);

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertEquals(500, $prestataire->fresh()->referral_credit_balance);
    }

    public function test_marking_a_subscription_payment_failed_immediately_cancels_it_and_restores_credit(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
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
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);
        $subscription->applyReferralCredit(true);

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $subscription->fresh()->total_charged,
            'status' => 'pending',
            'type' => 'subscription',
        ]);

        $payment->markAsFailed('{"status":"declined"}');

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertEquals(500, $prestataire->fresh()->referral_credit_balance);
    }
}
