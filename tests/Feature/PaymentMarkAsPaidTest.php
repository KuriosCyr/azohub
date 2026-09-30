<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
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

    // Corrigé suite à un 2e audit externe (suggestion de clôture de l'audit) : OrderCreate
    // réutilise une commande pending_payment plutôt que d'en recréer une à chaque tentative — un
    // onglet FedaPay resté ouvert depuis AVANT un changement de prix (le service a été modifié
    // puis re-validé entre-temps) pourrait sinon activer la commande à l'ancien montant, laissant
    // un escrow insuffisant pour ce qui est réellement dû au prestataire.
    public function test_a_payment_confirming_an_outdated_amount_is_flagged_instead_of_activating_the_order(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder();
        // Paiement initié AVANT que le prix de la commande ne change (ex. reprise via
        // OrderCreate après une modification du service).
        $payment = $this->makePayment($order, ['amount' => 8000]);

        $order->update(['amount' => 12000, 'prestataire_amount' => 10800]);

        $payment->markAsPaid('{"status":"approved"}');

        $this->assertSame('refund_pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $order->fresh()->status, 'La commande ne doit pas être activée avec un montant obsolète.');
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

    // Corrigé suite à un audit externe : un paiement d'abonnement confirmé tardivement, alors
    // qu'un abonnement plus récent est déjà actif, ne faisait qu'un Log::warning() — l'argent
    // restait "success" sans que rien ne le signale. Doit maintenant suivre le même traitement
    // que l'argent orphelin côté commande : marqué à rembourser, admin alerté.
    public function test_late_subscription_confirmation_superseded_by_a_newer_one_is_flagged(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ]);

        $abandonedSubscription = Subscription::create([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);

        // Un abonnement plus récent, actif, choisi entre-temps par le prestataire. created_at
        // forcé plus tard : deux créations dans le même test tombent facilement à la même
        // seconde (granularité de la colonne), ce qui ferait échouer la comparaison "> created_at".
        $newerSubscription = Subscription::create([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);
        $newerSubscription->created_at = now()->addMinute();
        $newerSubscription->save();

        $payment = Payment::create([
            'subscription_id' => $abandonedSubscription->id,
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 3000,
            'status' => 'pending',
            'type' => 'subscription',
        ]);

        $payment->markAsPaid('{"status":"approved"}');

        $this->assertSame('refund_pending', $payment->fresh()->status);
        Notification::assertSentTo($admin, AdminActionRequired::class);
    }

    // Corrigé suite à un second audit externe : contrairement à la commande orpheline (traitée
    // depuis le premier audit), un paiement d'abonnement confirmé APRÈS que l'abonnement ait été
    // annulé (expiré après 24h, ou paiement marqué échoué — voir Subscription::cancelAbandoned())
    // restait "success" pour toujours, sans remboursement prévu ni alerte admin.
    public function test_late_confirmation_on_a_cancelled_subscription_is_flagged_for_manual_refund(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => 3000,
            'status' => 'pending',
            'type' => 'subscription',
        ]);

        // Le prestataire a abandonné la page FedaPay, la tentative est annulée avant même que le
        // paiement ne se confirme (reproduit ExpireStalePendingPayments / markAsFailed).
        $subscription->cancelAbandoned();

        // FedaPay confirme quand même, en retard.
        $payment->markAsPaid('{"status":"approved"}');

        $this->assertSame('refund_pending', $payment->fresh()->status);
        $this->assertEquals(3000, $payment->fresh()->refund_amount_due);
        $this->assertSame('cancelled', $subscription->fresh()->status);
        Notification::assertSentTo($admin, AdminActionRequired::class);
    }
}
