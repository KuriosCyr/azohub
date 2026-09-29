<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Suggestion d'un audit externe, implémentée : un reçu PDF téléchargeable pour le client
// (ce qu'il a payé) et le prestataire (sa commission et son net), une fois un paiement
// réellement effectué.
class InvoiceDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function makePaidOrder(): Order
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'client_fee' => 500,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->total_charged,
            'status' => 'success',
            'type' => 'order_payment',
            'paid_at' => now(),
        ]);

        return $order;
    }

    public function test_client_can_download_a_receipt_once_paid(): void
    {
        $order = $this->makePaidOrder();

        $response = $this->actingAs($order->client)->get(route('orders.invoice', $order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_prestataire_can_download_an_invoice_once_paid(): void
    {
        $order = $this->makePaidOrder();

        $response = $this->actingAs($order->prestataire)->get(route('orders.invoice', $order));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_stranger_cannot_download_the_invoice(): void
    {
        $order = $this->makePaidOrder();
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)->get(route('orders.invoice', $order));

        $response->assertForbidden();
    }

    public function test_no_receipt_is_available_before_any_payment(): void
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($client)->get(route('orders.invoice', $order));

        $response->assertNotFound();
    }
}
