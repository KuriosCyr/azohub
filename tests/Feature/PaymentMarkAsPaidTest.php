<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\AdminActionRequired;
use App\Notifications\OrderConfirmed;
use App\Notifications\PaymentConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Couvre Payment::markAsPaid(), qui décide où va l'argent confirmé par le webhook FedaPay.
// Le cas normal (commande pending_payment -> paid) est simple ; les cas où la commande n'est
// PLUS pending_payment au moment où la confirmation arrive (annulée entre-temps, ou déjà
// financée par un autre paiement) sont ceux où une confirmation tardive peut faire disparaître
// de l'argent sans laisser de trace si on ne les traite pas explicitement.
class PaymentMarkAsPaidTest extends TestCase
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
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ], $overrides));
    }

    private function makePayment(Order $order, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'pending',
            'type' => 'order_payment',
        ], $overrides));
    }

    public function test_normal_payment_activates_the_order_and_notifies_both_parties(): void
    {
        Notification::fake();

        $order = $this->makeOrder();
        $payment = $this->makePayment($order);

        $payment->markAsPaid('{"status":"approved"}');

        $payment->refresh();
        $order->refresh();

        $this->assertSame('success', $payment->status);
        $this->assertSame('paid', $order->status);
        $this->assertSame('held', $order->payment_status);

        Notification::assertSentTo($order->prestataire, PaymentConfirmed::class);
        Notification::assertSentTo($order->client, OrderConfirmed::class);
    }

    public function test_late_confirmation_on_a_cancelled_order_is_flagged_for_manual_refund(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();
        $payment = $this->makePayment($order);

        // Le client a annulé pendant que la page FedaPay était encore ouverte dans un autre
        // onglet (reproduit OrderController::cancel() -> Order::refund()).
        $order->refund('Annulée par le client');

        $payment->markAsPaid('{"status":"approved"}');

        $payment->refresh();
        $order->refresh();

        $this->assertSame('refund_pending', $payment->status, 'L\'argent confirmé sur une commande annulée doit être marqué à rembourser, jamais laissé "success" sans suite.');
        $this->assertSame('cancelled', $order->status, 'La commande annulée ne doit pas être ressuscitée par une confirmation de paiement tardive.');

        Notification::assertSentTo($admin, AdminActionRequired::class);
    }

    public function test_double_payment_on_an_already_funded_order_is_flagged_for_manual_refund(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();

        // Deux onglets, deux transactions FedaPay distinctes pour la même commande.
        $firstPayment = $this->makePayment($order);
        $secondPayment = $this->makePayment($order);

        $firstPayment->markAsPaid('{"status":"approved"}');
        $order->refresh();
        $this->assertSame('paid', $order->status);

        $secondPayment->markAsPaid('{"status":"approved"}');
        $secondPayment->refresh();

        $this->assertSame('success', $firstPayment->fresh()->status);
        $this->assertSame('refund_pending', $secondPayment->status, 'Le deuxième paiement sur une commande déjà financée doit être marqué à rembourser.');

        Notification::assertSentTo($admin, AdminActionRequired::class);
    }

    public function test_subscription_payment_without_an_order_is_unaffected(): void
    {
        // Un paiement d'abonnement (pas de order_id) ne doit jamais passer par la branche
        // "commande non payable" — sinon tout paiement d'abonnement finirait en refund_pending.
        $user = User::factory()->create(['role' => 'prestataire']);

        $payment = Payment::create([
            'user_id' => $user->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 5000,
            'status' => 'pending',
            'type' => 'subscription',
        ]);

        $payment->markAsPaid('{"status":"approved"}');

        $this->assertSame('success', $payment->fresh()->status);
    }
}
