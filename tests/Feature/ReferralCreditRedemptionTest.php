<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Couvre la dépense du crédit de parrainage (User::redeemReferralCredit()/refundReferralCredit(),
// Order::applyReferralCredit(), Order::refund()) — jamais l'appel réel à FedaPay.
// PaymentService::initiateForOrder() n'est testée que sur son garde-fou de statut (qui lève AVANT
// tout appel réseau, cf. test dédié plus bas) — sa logique de réduction est extraite dans
// Order::applyReferralCredit() pour rester testable sans toucher à l'API, comme FedapayPayoutTest.
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
        // Le crédit s'applique pendant que la commande est encore 'pending_payment' (seul état où
        // applyReferralCredit() l'accepte, 4e audit) — elle passe 'paid' seulement ensuite.
        $order = $this->makeOrder($client);
        $order->applyReferralCredit(true);
        $order->update(['status' => 'paid', 'payment_status' => 'held']);
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

    // Corrigé suite à un 4e audit externe : applyReferralCredit() lisait `referral_credit_applied`
    // en mémoire (jamais rechargé sous verrou) avant de débiter puis d'écrire le crédit — une
    // commande annulée entre-temps (ex. expiration automatique pendant qu'une requête de paiement
    // concurrente était déjà en cours) pouvait quand même se voir appliquer le crédit après coup :
    // l'argent est réellement débité du solde client pour une commande qui ne sera jamais payée,
    // le crédit restant perdu pour toujours (rien ne rembourse une commande déjà 'cancelled').
    // Reproduit directement l'état "commande annulée entre le chargement et l'écriture" plutôt que
    // la vraie course concurrente (non simulable en mono-thread).
    public function test_applying_referral_credit_on_a_no_longer_payable_order_refunds_it_immediately(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        // La commande a déjà été annulée par l'expiration automatique au moment où
        // applyReferralCredit() s'exécute (l'objet $order en mémoire, lui, date d'avant).
        $order = $this->makeOrder($client, ['status' => 'cancelled', 'payment_status' => 'pending']);

        $applied = $order->applyReferralCredit(true);

        // Rien n'est resté appliqué sur la commande annulée, et le solde n'a pas bougé au final
        // (débité puis immédiatement restitué).
        $this->assertEquals(0, $applied);
        $this->assertEquals(0, $order->fresh()->referral_credit_applied);
        $this->assertEquals(300, $client->fresh()->referral_credit_balance);
    }

    // Corrigé suite à un 4e audit externe : PaymentService::initiateForOrder() touch()ait la
    // commande puis appliquait le crédit de parrainage sans jamais revérifier sous verrou que la
    // commande était toujours 'pending_payment' — un appelant (PaymentController::initiate()) ne
    // vérifie le statut qu'AVANT, sans verrou. Lève maintenant avant tout effet de bord (avant même
    // le touch()) si la commande n'est plus payable.
    public function test_initiate_for_order_refuses_a_no_longer_payable_order_before_any_side_effect(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client, ['status' => 'cancelled', 'payment_status' => 'pending']);
        $originalUpdatedAt = $order->fresh()->updated_at;

        try {
            app(PaymentService::class)->initiateForOrder($order, 'mtn_momo', true);
            $this->fail('Une exception était attendue.');
        } catch (\RuntimeException $e) {
            // Attendu.
        }

        $this->assertEquals(300, $client->fresh()->referral_credit_balance, 'Le crédit ne doit pas avoir été débité.');
        $this->assertEquals(
            $originalUpdatedAt->timestamp,
            $order->fresh()->updated_at->timestamp,
            'La commande ne doit pas avoir été touchée (ExpireStalePendingPayments ne doit pas la croire activement en cours de paiement).'
        );
    }
}
