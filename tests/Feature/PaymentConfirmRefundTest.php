<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Payment::confirmRefund() est le SEUL point d'entrée pour confirmer un remboursement (2e audit
// externe : un paiement "orphelin" — commande déjà dans un autre état, ou paiement d'abonnement
// sans commande — n'avait auparavant aucune action possible et restait indéfiniment dans
// "Remboursements à traiter"). Order::confirmRefund() a été retiré (3e audit externe) : il
// marquait "refunded" TOUS les paiements refund_pending d'une commande en un clic, ce qui pouvait
// résoudre à tort un paiement orphelin distinct partageant la même commande.
class PaymentConfirmRefundTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        return Order::create(array_merge([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 6000,
            'commission' => 600,
            'prestataire_amount' => 5400,
            'delivery_time' => 3,
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ], $overrides));
    }

    public function test_confirming_refund_on_an_orphaned_payment_resolves_it_independently_of_the_order(): void
    {
        // La commande n'est PAS en refund_pending (déjà annulée autrement).
        $order = $this->makeOrder();

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 6000,
            'status' => 'refund_pending',
            'refund_amount_due' => 6000,
            'type' => 'order_payment',
        ]);

        $payment->confirmRefund();

        $this->assertSame('refunded', $payment->fresh()->status);
    }

    // Enregistre désormais qui et quand (audit externe — 3e audit) : de l'argent sortant sans
    // aucune trace de qui a confirmé le remboursement n'était pas acceptable.
    public function test_confirming_a_refund_records_who_and_when(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 6000,
            'status' => 'refund_pending',
            'refund_amount_due' => 6000,
            'type' => 'order_payment',
        ]);

        $this->actingAs($admin);
        $payment->confirmRefund();

        $payment->refresh();
        $this->assertSame($admin->id, $payment->confirmed_refund_by);
        $this->assertNotNull($payment->confirmed_refund_at);
    }

    // Verrouillée + gardée par le statut (audit externe — 3e audit) : confirmer un paiement déjà
    // résolu (double clic, ou statut qui a changé entre-temps) ne doit rien faire, et surtout pas
    // écraser confirmed_refund_by/confirmed_refund_at d'une première confirmation légitime.
    public function test_confirming_an_already_refunded_payment_is_a_no_op(): void
    {
        $firstAdmin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 6000,
            'status' => 'refund_pending',
            'refund_amount_due' => 6000,
            'type' => 'order_payment',
        ]);

        $this->actingAs($firstAdmin);
        $payment->confirmRefund();
        $firstConfirmedAt = $payment->fresh()->confirmed_refund_at;

        $this->actingAs($secondAdmin);
        $payment->confirmRefund();

        $payment->refresh();
        $this->assertSame($firstAdmin->id, $payment->confirmed_refund_by, 'Un double clic ne doit pas réattribuer la confirmation à un autre admin.');
        $this->assertEquals($firstConfirmedAt, $payment->confirmed_refund_at);
    }

    // Corrigé suite à un 3e audit externe (remplace l'ancienne Order::confirmRefund(), retirée) :
    // un double paiement sur la même commande (deux onglets, un finance la commande, l'autre part
    // orphelin en refund_pending) ne doit résoudre QUE le paiement réellement confirmé par
    // l'admin — jamais l'autre, qui n'a en réalité pas été remboursé.
    public function test_confirming_one_payment_does_not_resolve_a_sibling_refund_pending_payment_on_the_same_order(): void
    {
        $order = $this->makeOrder(['payment_status' => 'refund_pending']);

        // Le paiement légitime, annulé après avoir financé la commande.
        $legitPayment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-LEGIT-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 6000,
            'status' => 'refund_pending',
            'refund_amount_due' => 6000,
            'type' => 'order_payment',
        ]);

        // Un second paiement orphelin distinct sur la MÊME commande (double paiement, deux
        // onglets), jamais réellement remboursé de son côté.
        $orphanPayment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-ORPHAN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 6000,
            'status' => 'refund_pending',
            'refund_amount_due' => 6000,
            'type' => 'order_payment',
        ]);

        $legitPayment->confirmRefund();

        $this->assertSame('refunded', $legitPayment->fresh()->status);
        $this->assertSame('refund_pending', $orphanPayment->fresh()->status, 'Le paiement orphelin distinct ne doit jamais être marqué remboursé à tort.');
        // La commande reste 'refund_pending' tant qu'il reste un paiement à traiter.
        $this->assertSame('refund_pending', $order->fresh()->payment_status);
    }

    // order.payment_status doit refléter la réalité une fois TOUS ses paiements résolus (audit
    // externe — 3e audit) : confirmer au niveau du paiement ne touchait auparavant jamais
    // order.payment_status, qui restait 'refund_pending' pour toujours (visible dans le compteur
    // de la barre latérale admin) même une fois l'unique paiement de la commande confirmé.
    public function test_confirming_the_only_refund_pending_payment_marks_the_order_refunded_too(): void
    {
        $order = $this->makeOrder(['payment_status' => 'refund_pending']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 6000,
            'status' => 'refund_pending',
            'refund_amount_due' => 6000,
            'type' => 'order_payment',
        ]);

        $payment->confirmRefund();

        $this->assertSame('refunded', $order->fresh()->payment_status);
    }
}
