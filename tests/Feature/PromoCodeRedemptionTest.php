<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Couvre les codes promo (PromoCode::discountFor()/hasReachedMaxUses()/alreadyUsedBy(),
// Order::applyPromoCode(), Order::refund()) — argent réel, jamais l'appel FedaPay lui-même
// (PaymentService reste non testé ici, comme pour le crédit de parrainage).
class PromoCodeRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $client, array $overrides = []): Order
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        return Order::create(array_merge([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'client_fee' => 500,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ], $overrides));
    }

    public function test_fixed_discount_reduces_the_total_by_a_flat_amount(): void
    {
        $code = PromoCode::create(['code' => 'FIXE500', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $error = $order->applyPromoCode('fixe500');

        $this->assertNull($error);
        $this->assertEquals(500, $order->fresh()->promo_discount_applied);
        $this->assertEquals(10000, $order->fresh()->total_charged);
        $this->assertSame($code->id, $order->fresh()->promo_code_id);
    }

    public function test_percentage_discount_is_computed_on_the_total_charged(): void
    {
        PromoCode::create(['code' => 'DIX', 'type' => 'percentage', 'value' => 10]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $order->applyPromoCode('DIX');

        // 10% de 10500 = 1050.
        $this->assertEquals(1050, $order->fresh()->promo_discount_applied);
        $this->assertEquals(9450, $order->fresh()->total_charged);
    }

    public function test_discount_never_brings_the_total_to_zero(): void
    {
        // Une remise de 100% laisse quand même 1 FCFA à payer (FedaPay refuse un montant nul).
        PromoCode::create(['code' => 'GRATUIT', 'type' => 'percentage', 'value' => 100]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $order->applyPromoCode('GRATUIT');

        $this->assertEquals(1, $order->fresh()->total_charged);
    }

    public function test_an_unknown_code_is_rejected_without_touching_the_order(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $error = $order->applyPromoCode('NEXISTEPAS');

        $this->assertNotNull($error);
        $this->assertEquals(0, $order->fresh()->promo_discount_applied);
        $this->assertNull($order->fresh()->promo_code_id);
    }

    public function test_an_inactive_code_is_rejected(): void
    {
        PromoCode::create(['code' => 'PERIME', 'type' => 'fixed', 'value' => 500, 'is_active' => false]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $error = $order->applyPromoCode('PERIME');

        $this->assertNotNull($error);
    }

    public function test_a_code_at_its_usage_cap_is_rejected_for_a_new_user(): void
    {
        $code = PromoCode::create(['code' => 'LIMITE1', 'type' => 'fixed', 'value' => 500, 'max_uses' => 1]);
        $firstClient = User::factory()->create(['role' => 'client']);
        $firstOrder = $this->makeOrder($firstClient);
        $this->assertNull($firstOrder->applyPromoCode('LIMITE1'));

        $secondClient = User::factory()->create(['role' => 'client']);
        $secondOrder = $this->makeOrder($secondClient);
        $error = $secondOrder->applyPromoCode('LIMITE1');

        $this->assertNotNull($error);
        $this->assertEquals(1, PromoCodeRedemption::where('promo_code_id', $code->id)->count());
    }

    public function test_the_same_user_cannot_use_a_code_twice(): void
    {
        PromoCode::create(['code' => 'UNEFOIS', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client']);
        $firstOrder = $this->makeOrder($client);
        $this->assertNull($firstOrder->applyPromoCode('UNEFOIS'));

        $secondOrder = $this->makeOrder($client);
        $error = $secondOrder->applyPromoCode('UNEFOIS');

        $this->assertNotNull($error);
        $this->assertEquals(0, $secondOrder->fresh()->promo_discount_applied);
    }

    public function test_applying_a_promo_code_is_idempotent_on_a_retried_order(): void
    {
        PromoCode::create(['code' => 'RETRY', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $order->applyPromoCode('RETRY');
        $order->applyPromoCode('RETRY');

        $this->assertEquals(1, PromoCodeRedemption::where('order_id', $order->id)->count());
        $this->assertEquals(500, $order->fresh()->promo_discount_applied);
    }

    public function test_promo_code_and_referral_credit_are_mutually_exclusive(): void
    {
        PromoCode::create(['code' => 'EXCLU', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client);

        $order->applyReferralCredit(true);
        $error = $order->applyPromoCode('EXCLU');

        $this->assertNotNull($error);
        $this->assertNull($order->fresh()->promo_code_id);
    }

    public function test_cancelling_a_never_paid_order_releases_the_promo_code(): void
    {
        $code = PromoCode::create(['code' => 'LIBERE', 'type' => 'fixed', 'value' => 500, 'max_uses' => 1]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);
        $order->applyPromoCode('LIBERE');

        $order->refund('Le client a changé d\'avis.');

        $this->assertEquals(0, PromoCodeRedemption::where('promo_code_id', $code->id)->count());
        $this->assertNull($order->fresh()->promo_code_id);
        $this->assertEquals(0, $order->fresh()->promo_discount_applied);
        $this->assertFalse($code->fresh()->hasReachedMaxUses());

        // Le même client peut donc réutiliser le code sur une nouvelle commande.
        $newOrder = $this->makeOrder($client);
        $this->assertNull($newOrder->applyPromoCode('LIBERE'));
    }

    public function test_cancelling_a_paid_order_keeps_the_promo_code_consumed(): void
    {
        $code = PromoCode::create(['code' => 'GARDE', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client, ['status' => 'paid', 'payment_status' => 'held']);
        $order->applyPromoCode('GARDE');
        $order->payments()->create([
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->fresh()->total_charged,
            'status' => 'success',
            'type' => 'order_payment',
        ]);

        $order->refund('Litige tranché en faveur du client.');

        $this->assertEquals(1, PromoCodeRedemption::where('promo_code_id', $code->id)->count());
        $this->assertSame($code->id, $order->fresh()->promo_code_id);
    }
}
