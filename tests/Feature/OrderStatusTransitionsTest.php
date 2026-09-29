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
}
