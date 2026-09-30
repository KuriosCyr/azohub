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

    // Corrigé suite à un audit externe : une remise de 100% était auparavant plafonnée
    // seulement pour laisser 1 FCFA à payer (FedaPay), sans jamais vérifier que la réduction ne
    // dépasse pas la marge d'Azohub (commission + frais client = 1000 + 500 = 1500 ici) —
    // prestataire_amount restant fixé sur le prix plein, Azohub payait la différence de sa poche.
    public function test_discount_is_capped_to_azohubs_margin_not_just_1_fcfa(): void
    {
        PromoCode::create(['code' => 'GRATUIT', 'type' => 'percentage', 'value' => 100]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $order->applyPromoCode('GRATUIT');

        // Réduction plafonnée à 1500 (la marge), pas aux 10499 que "100% - 1 FCFA" aurait permis.
        $this->assertEquals(1500, $order->fresh()->promo_discount_applied);
        $this->assertEquals(9000, $order->fresh()->total_charged);
    }

    public function test_a_large_fixed_discount_is_also_capped_to_the_margin(): void
    {
        PromoCode::create(['code' => 'ENORME', 'type' => 'fixed', 'value' => 8000]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $order->applyPromoCode('ENORME');

        $this->assertEquals(1500, $order->fresh()->promo_discount_applied);
    }

    public function test_an_expired_code_is_rejected(): void
    {
        PromoCode::create(['code' => 'PERIME2', 'type' => 'fixed', 'value' => 500, 'expires_at' => now()->subDay()]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client);

        $error = $order->applyPromoCode('PERIME2');

        $this->assertNotNull($error);
        $this->assertEquals(0, $order->fresh()->promo_discount_applied);
    }

    public function test_a_code_below_its_minimum_order_amount_is_rejected(): void
    {
        PromoCode::create(['code' => 'GROSSE', 'type' => 'fixed', 'value' => 500, 'min_order_amount' => 50000]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client); // total_charged = 10500, sous le minimum de 50000

        $error = $order->applyPromoCode('GROSSE');

        $this->assertNotNull($error);
        $this->assertEquals(0, $order->fresh()->promo_discount_applied);
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

    // Corrigé suite à un 4e audit externe : applyPromoCode() ne revérifiait jamais que la commande
    // était toujours 'pending_payment' avant d'appliquer la réduction et de créer la
    // PromoCodeRedemption — une commande annulée entre-temps (ex. expiration automatique pendant
    // qu'une tentative de paiement concurrente était en cours) consommait quand même un usage du
    // code, bloquant ce client de le réutiliser sur une commande qui ne sera jamais payée.
    public function test_applying_a_promo_code_on_a_no_longer_payable_order_is_rejected_without_consuming_it(): void
    {
        $code = PromoCode::create(['code' => 'PERIME3', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeOrder($client, ['status' => 'cancelled', 'payment_status' => 'pending']);

        $error = $order->applyPromoCode('PERIME3');

        $this->assertNotNull($error);
        $this->assertNull($order->fresh()->promo_code_id);
        $this->assertEquals(0, PromoCodeRedemption::where('promo_code_id', $code->id)->count());

        // Le code reste utilisable par ce même client sur une nouvelle commande.
        $newOrder = $this->makeOrder($client);
        $this->assertNull($newOrder->applyPromoCode('PERIME3'));
    }

    // Trou symétrique corrigé (5e audit externe) : applyPromoCode() ne revérifiait pas
    // referral_credit_applied sous verrou — un crédit de parrainage appliqué par une requête
    // concurrente entre le chargement de cette copie et cet appel ne devait plus pouvoir être
    // court-circuité (l'exclusion mutuelle doit tenir même entre deux copies PHP distinctes).
    public function test_applying_a_promo_code_after_referral_credit_was_applied_concurrently_is_rejected(): void
    {
        $code = PromoCode::create(['code' => 'CONCURRENT2', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeOrder($client);
        $concurrentRequest = Order::find($order->id);

        $order->applyReferralCredit(true);
        $error = $concurrentRequest->applyPromoCode('CONCURRENT2');

        $this->assertNotNull($error);
        $this->assertNull($order->fresh()->promo_code_id);
        $this->assertEquals(0, PromoCodeRedemption::where('promo_code_id', $code->id)->count());
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
        // Le code s'applique pendant que la commande est encore 'pending_payment' (seul état où
        // applyPromoCode() l'accepte, 4e audit) — elle passe 'paid' seulement ensuite.
        $order = $this->makeOrder($client);
        $order->applyPromoCode('GARDE');
        $order->update(['status' => 'paid', 'payment_status' => 'held']);
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
