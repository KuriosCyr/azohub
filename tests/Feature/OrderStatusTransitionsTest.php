<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Régression après verrouillage (lockForUpdate) des transitions accept/cancel/deliver/
// requestRevision (audit externe : ces transitions lisaient puis écrivaient le statut sans
// verrou, exposées à un chevauchement, ex. annulation client + acceptation prestataire
// simultanées). Le verrouillage ne se prouve pas par un test mono-thread, mais le comportement
// fonctionnel (cas normal + statut déjà invalide) doit rester identique après le refactor.
class OrderStatusTransitionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        return Order::create(array_merge([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ], $overrides));
    }

    public function test_prestataire_can_accept_a_paid_order(): void
    {
        Notification::fake();
        $order = $this->makeOrder();

        $response = $this->actingAs($order->prestataire)->post(route('orders.accept', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_accepting_an_order_that_is_no_longer_paid_is_rejected(): void
    {
        $order = $this->makeOrder(['status' => 'cancelled']);

        $response = $this->actingAs($order->prestataire)->post(route('orders.accept', $order));

        $response->assertSessionHas('error');
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_client_can_cancel_a_pending_payment_order(): void
    {
        Notification::fake();
        $order = $this->makeOrder(['status' => 'pending_payment', 'payment_status' => 'pending']);

        $response = $this->actingAs($order->client)
            ->post(route('orders.cancel', $order), ['cancellation_reason' => 'Changement de plan.']);

        $response->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancelling_an_order_already_in_progress_is_rejected(): void
    {
        $order = $this->makeOrder(['status' => 'in_progress']);

        $response = $this->actingAs($order->client)
            ->post(route('orders.cancel', $order), ['cancellation_reason' => 'Trop tard.']);

        $response->assertSessionHas('error');
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_prestataire_can_mark_an_in_progress_order_as_delivered(): void
    {
        Notification::fake();
        $order = $this->makeOrder(['status' => 'in_progress']);

        $response = $this->actingAs($order->prestataire)
            ->post(route('orders.deliver', $order), ['delivery_notes' => 'Voici le travail.']);

        $response->assertRedirect();
        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
        $this->assertNotNull($order->validation_deadline);
    }

    public function test_marking_a_not_yet_accepted_order_as_delivered_is_rejected(): void
    {
        $order = $this->makeOrder(['status' => 'paid']);

        $response = $this->actingAs($order->prestataire)
            ->post(route('orders.deliver', $order), ['delivery_notes' => 'Trop tôt.']);

        $response->assertSessionHas('error');
        $this->assertSame('paid', $order->fresh()->status);
    }

    // refuse() ne verrouillait pas la commande avant d'appeler refund() (audit externe) —
    // corrigé pour suivre le même verrouillage que accept()/cancel()/deliver().
    public function test_prestataire_can_refuse_a_paid_order(): void
    {
        Notification::fake();
        $order = $this->makeOrder();

        $response = $this->actingAs($order->prestataire)
            ->post(route('orders.refuse', $order), ['refusal_reason' => 'Trop de travail en cours.']);

        $response->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_refusing_an_order_that_is_no_longer_paid_is_rejected(): void
    {
        $order = $this->makeOrder(['status' => 'in_progress']);

        $response = $this->actingAs($order->prestataire)
            ->post(route('orders.refuse', $order), ['refusal_reason' => 'Trop tard.']);

        $response->assertSessionHas('error');
        $this->assertSame('in_progress', $order->fresh()->status);
    }

    public function test_client_can_validate_a_delivered_order(): void
    {
        Notification::fake();
        $order = $this->makeOrder(['status' => 'delivered', 'delivered_at' => now()]);

        $response = $this->actingAs($order->client)->post(route('orders.validate', $order));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('released', $order->fresh()->payment_status);
    }

    // Corrigé suite à un 2e audit externe : releasePayment() peut échouer en silence (déjà
    // traité, ou commande en litige) — validate() ne doit plus envoyer "le prestataire a reçu
    // son paiement" quand ce n'est pas vraiment arrivé.
    public function test_validating_an_order_that_cannot_be_released_shows_an_error_not_a_false_success(): void
    {
        Notification::fake();
        // 'delivered' pour passer le premier contrôle du contrôleur, mais déjà en remboursement
        // à traiter — releasePayment() doit refuser en interne, sous son propre verrou.
        $order = $this->makeOrder(['status' => 'delivered', 'delivered_at' => now(), 'payment_status' => 'refund_pending']);

        $response = $this->actingAs($order->client)->post(route('orders.validate', $order));

        $response->assertSessionHas('error');
        Notification::assertNothingSent();
    }

    // DisputeController::store() ne verrouillait pas la commande avant de créer le litige
    // (audit externe) — une commande qui vient d'être validée/complétée ne doit plus pouvoir
    // passer en 'disputed' après coup.
    public function test_opening_a_dispute_on_a_completed_order_is_rejected(): void
    {
        $order = $this->makeOrder(['status' => 'completed', 'payment_status' => 'released']);

        $response = $this->actingAs($order->client)->post(route('orders.dispute.store', $order), [
            'reason' => 'work_not_delivered',
            'description' => str_repeat('Le travail n\'a jamais été livré. ', 2),
        ]);

        $response->assertSessionHas('error');
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertNull($order->fresh()->dispute);
    }

    public function test_opening_a_dispute_on_an_in_progress_order_succeeds(): void
    {
        Notification::fake();
        $order = $this->makeOrder(['status' => 'in_progress']);

        $response = $this->actingAs($order->client)->post(route('orders.dispute.store', $order), [
            'reason' => 'work_not_delivered',
            'description' => str_repeat('Le travail n\'a jamais été livré. ', 2),
        ]);

        $response->assertRedirect();
        $this->assertSame('disputed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->dispute);
    }
}
