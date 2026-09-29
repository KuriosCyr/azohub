<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Suggestion du client (implémentée) : le nombre de révisions incluses est fixé sur le service
// (ou l'offre personnalisée) par le prestataire, recopié sur la commande à sa création, et
// limité — sans quoi un client de mauvaise foi pouvait bloquer indéfiniment le paiement du
// prestataire en enchaînant les demandes de révision (relevé par un audit externe).
class OrderRevisionLimitTest extends TestCase
{
    use RefreshDatabase;

    private function makeDeliveredOrder(int $revisionsIncluded = 2, int $revisionsUsed = 0): Order
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        return Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'delivered',
            'payment_status' => 'held',
            'revisions_included' => $revisionsIncluded,
            'revisions_used' => $revisionsUsed,
        ]);
    }

    public function test_client_can_request_a_revision_within_the_included_quota(): void
    {
        $order = $this->makeDeliveredOrder(revisionsIncluded: 2, revisionsUsed: 0);

        $response = $this->actingAs($order->client)
            ->post(route('orders.request-revision', $order), ['revision_notes' => 'Merci de revoir la couleur du logo.']);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('in_progress', $order->status);
        $this->assertSame(1, $order->revisions_used);
    }

    public function test_client_cannot_request_a_revision_once_the_quota_is_exhausted(): void
    {
        $order = $this->makeDeliveredOrder(revisionsIncluded: 2, revisionsUsed: 2);

        $response = $this->actingAs($order->client)
            ->post(route('orders.request-revision', $order), ['revision_notes' => 'Encore une modification svp.']);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $order->refresh();
        // La commande reste "delivered" (pas repassée en in_progress) et le compteur ne bouge pas.
        $this->assertSame('delivered', $order->status);
        $this->assertSame(2, $order->revisions_used);
    }

    public function test_can_request_revision_helper_matches_the_quota(): void
    {
        $order = $this->makeDeliveredOrder(revisionsIncluded: 1, revisionsUsed: 0);
        $this->assertTrue($order->canRequestRevision());

        $order->update(['revisions_used' => 1]);
        $this->assertFalse($order->fresh()->canRequestRevision());
    }

    public function test_default_revisions_included_constant_is_used_as_fallback(): void
    {
        $this->assertSame(2, Order::DEFAULT_REVISIONS_INCLUDED);
    }
}
