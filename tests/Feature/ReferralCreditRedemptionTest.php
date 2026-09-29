<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Couvre la dépense du crédit de parrainage (User::redeemReferralCredit()/refundReferralCredit(),
// Order::applyReferralCredit(), Order::refund()) — jamais l'appel réel à FedaPay
// (PaymentService::initiateForOrder ne teste que sa logique de réduction, extraite dans
// Order::applyReferralCredit() pour rester testable sans toucher à l'API, comme FedapayPayoutTest).
class ReferralCreditRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $client, array $overrides = []): Order
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        return Order::create(array_merge([
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
    }

    public function test_redeeming_never_exceeds_the_available_balance(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 150]);

        $redeemed = $client->redeemReferralCredit(500);

        $this->assertEquals(150, $redeemed);
        $this->assertEquals(0, $client->fresh()->referral_credit_balance);
    }

    public function test_redeeming_less_than_the_balance_only_consumes_what_was_asked(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 500]);

        $redeemed = $client->redeemReferralCredit(150);

        $this->assertEquals(150, $redeemed);
        $this->assertEquals(350, $client->fresh()->referral_credit_balance);
    }

    public function test_refunding_credit_adds_it_back(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 100]);

        $client->refundReferralCredit(150);

        $this->assertEquals(250, $client->fresh()->referral_credit_balance);
    }

    public function test_applying_referral_credit_reduces_the_total_charged_and_leaves_at_least_1_fcfa(): void
    {
        // Prix 10000 + frais 500 = 10500 à payer ; largement plus de crédit que nécessaire.
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 50000]);
        $order = $this->makeOrder($client);

        $applied = $order->applyReferralCredit(true);

        $this->assertEquals(10499, $applied);
        $this->assertEquals(10499, $order->fresh()->referral_credit_applied);
        $this->assertEquals(1, $order->fresh()->total_charged);
        // Seul ce qui a été utilisé quitte le solde du client — pas tout le solde disponible.
        $this->assertEquals(50000 - 10499, $client->fresh()->referral_credit_balance);
    }

    public function test_applying_referral_credit_smaller_than_the_order_only_consumes_that_much(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client);

        $applied = $order->applyReferralCredit(true);

        $this->assertEquals(300, $applied);
        $this->assertEquals(10500 - 300, $order->fresh()->total_charged);
        $this->assertEquals(0, $client->fresh()->referral_credit_balance);
    }

    public function test_applying_referral_credit_is_idempotent_on_a_retried_order(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client);

        $order->applyReferralCredit(true);
        $order->applyReferralCredit(true);

        $this->assertEquals(300, $order->fresh()->referral_credit_applied);
        $this->assertEquals(0, $client->fresh()->referral_credit_balance);
    }

    public function test_not_requesting_referral_credit_applies_nothing(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client);

        $applied = $order->applyReferralCredit(false);

        $this->assertEquals(0, $applied);
        $this->assertEquals(300, $client->fresh()->referral_credit_balance);
    }

    public function test_cancelling_a_never_paid_order_restores_the_applied_credit(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client);
        $order->applyReferralCredit(true);

        $order->refund('Le client a changé d\'avis.');

        $this->assertEquals(300, $client->fresh()->referral_credit_balance);
        $this->assertEquals(0, $order->fresh()->referral_credit_applied);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancelling_a_paid_order_does_not_restore_the_credit_it_already_paid_for(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client, ['status' => 'paid', 'payment_status' => 'held']);
        $order->applyReferralCredit(true);
        $order->payments()->create([
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->fresh()->total_charged,
            'status' => 'success',
            'type' => 'order_payment',
        ]);

        $order->refund('Litige tranché en faveur du client.');

        // Le crédit a bel et bien servi à payer cette commande : il n'est pas restitué ici (le
        // remboursement du paiement lui-même suit son propre circuit, cf. confirmRefund()).
        $this->assertEquals(0, $client->fresh()->referral_credit_balance);
        $this->assertEquals(300, $order->fresh()->referral_credit_applied);
        $this->assertSame('refund_pending', $order->fresh()->payment_status);
    }
}
