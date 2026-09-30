<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Corrige un point signalé par un 2e audit externe : Order::confirmRefund() ne résolvait un
// paiement que via sa commande (order.payment_status = 'refund_pending') — un paiement
// "orphelin" (commande déjà dans un autre état, ou paiement d'abonnement sans commande) n'avait
// aucune action possible et restait indéfiniment dans "Remboursements à traiter".
class PaymentConfirmRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_refund_on_an_orphaned_payment_resolves_it_independently_of_the_order(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        // La commande n'est PAS en refund_pending (déjà annulée autrement) : Order::
        // confirmRefund() ne trouverait rien à faire ici.
        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 6000,
            'commission' => 600,
            'prestataire_amount' => 5400,
            'delivery_time' => 3,
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
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
}
