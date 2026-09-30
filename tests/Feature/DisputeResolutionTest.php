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

    public function test_partial_refund_splits_between_client_and_prestataire_proportionally(): void
    {
        // Commande à 10 000 FCFA, prestataire_amount = 9 000. Remboursement partiel de 4 000
        // FCFA au client (40% du prix) : le prestataire doit recevoir 60% de sa part, soit 5 400.
        $order = $this->makeDisputedOrder();
        $dispute = $order->dispute;
        $prestataire = $order->prestataire;

        $dispute->resolve('partial_refund', $this->adminUser->id, 4000.0);

        $order->refresh();
        $prestataire->refresh();

        $this->assertSame('completed', $order->status);
        $this->assertSame('refund_pending', $order->payment_status);
        $this->assertEquals(5400, $prestataire->wallet_balance);
        $this->assertSame('refund_pending', $order->payments()->latest()->first()->status);

        $dispute->refresh();
        $this->assertEquals(4000, $dispute->refund_amount);
    }

    // Corrigé suite à un audit externe : le remboursement demandé ne peut jamais dépasser ce que
    // le client a réellement payé (total_charged), même si l'appelant demande plus — sans ce
    // filet, un code promo ou du crédit de parrainage appliqué aurait permis de "rembourser" plus
    // que ce que FedaPay a réellement encaissé.
    public function test_partial_refund_is_capped_to_what_the_client_actually_paid(): void
    {
        // Prix 10000 + frais 500 - réduction 2000 = total_charged 8500 réellement payé.
        $order = $this->makeDisputedOrder(['client_fee' => 500, 'promo_discount_applied' => 2000]);
        $dispute = $order->dispute;

        // L'admin demande 9000 (plus que ce que le client a payé) — doit être plafonné à 8500.
        $dispute->resolve('partial_refund', $this->adminUser->id, 9000.0);

        $payment = $order->fresh()->payments()->latest()->first();
        $this->assertEquals(8500, $payment->refund_amount_due);
    }

    // Corrigé suite à un second audit externe : le ratio du remboursement partiel se basait sur
    // order.amount (le prix affiché), pas sur ce qu'Azohub a réellement encaissé (total_charged).
    // Avec une réduction appliquée, ça faisait payer au prestataire une part calculée sur un
    // montant plus gros que ce qui avait été perçu — Azohub reversait alors plus que ce qu'il
    // avait encaissé. Reproduit l'exemple exact de l'audit : prix 10000, commission 1500,
    // frais 500, réduction 2000 → 8500 réellement encaissés.
    public function test_partial_refund_never_pays_out_more_than_azohub_collected(): void
    {
        $order = $this->makeDisputedOrder([
            'commission' => 1500,
            'prestataire_amount' => 8500,
            'client_fee' => 500,
            'promo_discount_applied' => 2000,
        ]);
        $dispute = $order->dispute;
        $prestataire = $order->prestataire;

        // Remboursement total des 8500 réellement payés : le prestataire ne doit plus rien
        // recevoir (avant le correctif, il recevait encore 1275 FCFA en trop).
        $dispute->resolve('partial_refund', $this->adminUser->id, 8500.0);

        $prestataire->refresh();
        $this->assertEquals(0, $prestataire->wallet_balance);
    }

    public function test_a_genuinely_partial_refund_with_a_discount_splits_without_any_loss(): void
    {
        $order = $this->makeDisputedOrder([
            'commission' => 1500,
            'prestataire_amount' => 8500,
            'client_fee' => 500,
            'promo_discount_applied' => 2000,
        ]);
        $dispute = $order->dispute;
        $prestataire = $order->prestataire;

        // 5000 sur 8500 réellement encaissés rendus au client ; le prestataire reçoit le reste
        // proportionnel (3500) — total versé (8500) égal à ce qu'Azohub a collecté, jamais plus.
        $dispute->resolve('partial_refund', $this->adminUser->id, 5000.0);

        $prestataire->refresh();
        $this->assertEquals(3500, $prestataire->wallet_balance);
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

    // Corrigé suite à un audit externe : "no_action" remettait la commande en 'delivered' sans
    // toucher à validation_deadline, qui restait sur son ancienne valeur (déjà expirée ou
    // presque, le litige ayant pris du temps à traiter) — l'auto-validation pouvait alors se
    // déclencher dans l'heure suivante au lieu de laisser au client le temps normal de vérifier.
    public function test_no_action_resets_the_validation_deadline_when_returning_to_delivered(): void
    {
        $order = $this->makeDisputedOrder([
            'delivered_at' => now()->subHours(70),
            'validation_deadline' => now()->subHour(), // déjà expiré
        ]);
        $dispute = $order->dispute;

        $dispute->resolve('no_action', $this->adminUser->id);

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertTrue($order->validation_deadline->isFuture());
    }

    // Corrigé suite à un audit externe : une résolution "rembourser le client" pouvait s'exécuter
    // après qu'une libération de paiement concurrente ait déjà payé le prestataire, écrasant
    // silencieusement payment_status='released' en 'refund_pending' — payant les deux parties.
    public function test_refund_client_does_not_touch_an_order_already_released(): void
    {
        $order = $this->makeDisputedOrder();
        $dispute = $order->dispute;

        // Simule la course : le paiement a déjà été libéré au prestataire juste avant que la
        // résolution du litige ne s'exécute.
        $order->update(['payment_status' => 'released']);

        $dispute->resolve('refund_client', $this->adminUser->id);

        $order->refresh();
        $this->assertSame('released', $order->payment_status, 'Ne doit jamais écraser released en refund_pending.');
        $this->assertNotSame('cancelled', $order->status);
    }

    // Corrigé suite à un audit externe : releasePayment() ne vérifiait que payment_status, pas
    // le statut 'disputed' — une validation client ou l'auto-validation pouvait donc libérer le
    // paiement d'une commande qui venait de passer en litige.
    public function test_release_payment_is_blocked_on_a_disputed_order_by_default(): void
    {
        $order = $this->makeDisputedOrder();
        $prestataire = $order->prestataire;

        $order->releasePayment();

        $order->refresh();
        $prestataire->refresh();
        $this->assertSame('held', $order->payment_status);
        $this->assertEquals(0, $prestataire->wallet_balance);
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
