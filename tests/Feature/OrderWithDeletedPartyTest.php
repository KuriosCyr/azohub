<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Corrigé suite à un 7e audit externe : si un prestataire (ou un client) est supprimé (soft
// delete, ex. via l'ancienne action admin qui contournait les garde-fous) alors qu'il a encore
// une commande active, Order::prestataire()/client() (belongsTo standard, sans withTrashed())
// renvoyaient null — releasePayment() passait quand même payment_status à 'released' SANS
// créditer personne (if ($order->prestataire) ne s'exécutait jamais, l'argent était marqué
// "versé" sans destinataire), et ValidateExpiredOrders plantait sur
// $order->prestataire->notify(...), interrompant toute la boucle pour les commandes suivantes.
class OrderWithDeletedPartyTest extends TestCase
{
    use RefreshDatabase;

    private function makeDeliveredOrder(): Order
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 0, 'completed_orders' => 0]);

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
            'delivered_at' => now(),
            'validation_deadline' => now()->subHour(),
        ]);
    }

    public function test_release_payment_still_credits_the_wallet_when_the_prestataire_was_soft_deleted(): void
    {
        $order = $this->makeDeliveredOrder();
        $prestataire = $order->prestataire;
        $prestataire->delete();

        $released = $order->releasePayment(autoValidated: true);

        $this->assertTrue($released);
        $this->assertSame('released', $order->fresh()->payment_status);
        $this->assertEquals(9000, $prestataire->fresh()->wallet_balance, 'L\'argent ne doit pas être marqué "versé" sans que personne ne soit réellement crédité.');
    }

    public function test_validate_expired_orders_does_not_crash_and_still_processes_the_rest_of_the_batch(): void
    {
        Notification::fake();
        $orderWithDeletedPrestataire = $this->makeDeliveredOrder();
        $orderWithDeletedPrestataire->prestataire->delete();

        $normalOrder = $this->makeDeliveredOrder();

        $this->artisan('orders:validate-expired')->assertExitCode(0);

        $this->assertSame('released', $orderWithDeletedPrestataire->fresh()->payment_status);
        $this->assertSame(
            'released',
            $normalOrder->fresh()->payment_status,
            'Les commandes suivantes du lot doivent être traitées même si une autre pose un problème.'
        );
    }
}
