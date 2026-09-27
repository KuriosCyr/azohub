<?php

namespace Tests\Feature;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Vérifie les 4 décisions possibles de Dispute::resolve() (utilisée par l'action
// "Résoudre" du panel admin) : chacune doit produire exactement le mouvement
// d'argent/statut attendu, et une résolution déjà traitée ne doit plus rien changer
// (garde-fou contre un double clic admin qui déclencherait deux mouvements contradictoires).
class DisputeResolutionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDisputedOrder(array $orderOverrides = []): Order
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 0, 'completed_orders' => 0]);
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Order::create(array_merge([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'disputed',
            'payment_status' => 'held',
        ], $orderOverrides));

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'success',
            'type' => 'order_payment',
        ]);

        Dispute::create([
            'order_id' => $order->id,
            'opened_by' => $client->id,
            'reason' => 'work_not_delivered',
            'description' => str_repeat('Le travail n\'a jamais été livré. ', 2),
            'status' => 'open',
        ]);

        $this->adminUser = $admin;

        return $order;
    }

    private User $adminUser;

    public function test_refund_client_cancels_order_and_marks_payment_refund_pending(): void
    {
        $order = $this->makeDisputedOrder();
        $dispute = $order->dispute;

        $dispute->resolve('refund_client', $this->adminUser->id);

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('refund_pending', $order->payment_status);
        $this->assertSame('refund_pending', $order->payments()->latest()->first()->status);

        $dispute->refresh();
        $this->assertSame('resolved', $dispute->status);
        $this->assertSame('refund_client', $dispute->resolution);
        $this->assertSame($this->adminUser->id, $dispute->resolved_by);
        $this->assertNotNull($dispute->resolved_at);
    }

    public function test_partial_refund_behaves_like_refund_client_on_the_order(): void
    {
        $order = $this->makeDisputedOrder();
        $dispute = $order->dispute;

        $dispute->resolve('partial_refund', $this->adminUser->id);

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('refund_pending', $order->payment_status);
    }

    public function test_pay_prestataire_completes_order_and_credits_wallet_immediately(): void
    {
        $order = $this->makeDisputedOrder();
        $dispute = $order->dispute;
        $prestataire = $order->prestataire;

        $dispute->resolve('pay_prestataire', $this->adminUser->id);

        $order->refresh();
        $prestataire->refresh();

        $this->assertSame('completed', $order->status);
        $this->assertSame('released', $order->payment_status);
        $this->assertEquals(9000, $prestataire->wallet_balance);
        $this->assertSame(1, $prestataire->completed_orders);
    }

    public function test_no_action_reverts_order_to_in_progress_when_not_yet_delivered(): void
    {
        $order = $this->makeDisputedOrder(['delivered_at' => null]);
        $dispute = $order->dispute;

        $dispute->resolve('no_action', $this->adminUser->id);

        $order->refresh();
        $this->assertSame('in_progress', $order->status);
        // Aucun mouvement d'argent : le solde n'a pas bougé.
        $this->assertSame('held', $order->payment_status);
    }

    public function test_no_action_reverts_order_to_delivered_when_already_delivered(): void
    {
        $order = $this->makeDisputedOrder(['delivered_at' => now()]);
        $dispute = $order->dispute;

        $dispute->resolve('no_action', $this->adminUser->id);

        $order->refresh();
        $this->assertSame('delivered', $order->status);
    }

    public function test_resolving_an_already_resolved_dispute_is_a_no_op(): void
    {
        $order = $this->makeDisputedOrder();
        $dispute = $order->dispute;
        $prestataire = $order->prestataire;

        $dispute->resolve('pay_prestataire', $this->adminUser->id);
        $prestataire->refresh();
        $this->assertEquals(9000, $prestataire->wallet_balance);

        // Un deuxième clic admin (ex. double clic, ou tentative de changer la décision)
        // ne doit surtout pas créditer le portefeuille une deuxième fois.
        $dispute->resolve('refund_client', $this->adminUser->id);

        $order->refresh();
        $prestataire->refresh();
        $this->assertSame('completed', $order->status, 'La commande ne doit pas repasser cancelled après résolution.');
        $this->assertEquals(9000, $prestataire->wallet_balance, 'Le portefeuille ne doit pas être crédité deux fois.');
    }
}
