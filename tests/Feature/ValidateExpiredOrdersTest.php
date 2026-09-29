<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderAutoValidated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Suite à un audit externe : orders:validate-expired libérait bien le paiement au prestataire
// (Order::releasePayment(autoValidated: true)), mais ne prévenait ni le client ni le
// prestataire — le prestataire découvrait le paiement crédité sans explication.
class ValidateExpiredOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_validating_an_expired_order_notifies_both_client_and_prestataire(): void
    {
        Notification::fake();

        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 0]);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'delivered',
            'payment_status' => 'held',
            'auto_validated' => false,
            'validation_deadline' => now()->subHour(),
        ]);

        $this->artisan('orders:validate-expired')->assertExitCode(0);

        $order->refresh();
        $prestataire->refresh();

        $this->assertSame('completed', $order->status);
        $this->assertSame('released', $order->payment_status);
        $this->assertTrue((bool) $order->auto_validated);
        $this->assertEquals(9000, $prestataire->wallet_balance);

        Notification::assertSentTo($client, OrderAutoValidated::class, fn ($n) => $n->forClient === true);
        Notification::assertSentTo($prestataire, OrderAutoValidated::class, fn ($n) => $n->forClient === false);
    }

    public function test_orders_not_yet_expired_are_left_untouched(): void
    {
        Notification::fake();

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
            'status' => 'delivered',
            'payment_status' => 'held',
            'auto_validated' => false,
            'validation_deadline' => now()->addDay(),
        ]);

        $this->artisan('orders:validate-expired')->assertExitCode(0);

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        Notification::assertNothingSent();
    }
}
