<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Corrige un manque identifié par un audit externe : ni les commandes jamais payées, ni les
// tentatives d'abonnement jamais confirmées, n'expiraient automatiquement — le crédit de
// parrainage consommé dessus restait perdu pour toujours (aucun argent n'ayant jamais été
// réellement débité pour ces tentatives).
class ExpireStalePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaleOrder(User $client, array $overrides = []): Order
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create(array_merge([
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

        $order->created_at = now()->subHours(25);
        $order->save();
        $this->backdateUpdatedAt($order, 25);

        return $order;
    }

    // save() réécrit toujours updated_at sur "maintenant", même quand on vient d'assigner une
    // autre valeur explicitement (Eloquent le fait systématiquement dans updateTimestamps()) —
    // un DB::table()->update() brut est le seul moyen fiable de forcer une valeur précise.
    // L'expiration se base maintenant sur updated_at (audit externe — 2e audit), donc tout appel
    // ultérieur qui modifie le modèle (ex. applyReferralCredit()) doit être suivi d'un nouveau
    // backdate pour que le test simule bien "plus aucune activité depuis 25h".
    private function backdateUpdatedAt(Order|Subscription $model, int $hours): void
    {
        DB::table($model->getTable())->where('id', $model->id)->update(['updated_at' => now()->subHours($hours)]);
    }

    private function makeStaleSubscription(User $prestataire, array $overrides = []): Subscription
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create(array_merge([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ], $overrides));

        $subscription->created_at = now()->subHours(25);
        $subscription->save();
        $this->backdateUpdatedAt($subscription, 25);

        return $subscription;
    }

    public function test_a_stale_unpaid_order_is_cancelled_and_restores_its_referral_credit(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeStaleOrder($client);
        $order->applyReferralCredit(true);
        $this->backdateUpdatedAt($order, 25);

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertEquals(300, $client->fresh()->referral_credit_balance);
    }

    public function test_a_recent_unpaid_order_is_left_untouched(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeStaleOrder($client);
        $order->created_at = now()->subHours(1);
        $order->save();

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('pending_payment', $order->fresh()->status);
    }

    // Corrigé suite à un 2e audit externe : la tâche se basait sur created_at, pas sur la
    // dernière tentative de paiement — une commande reprise (revenue via OrderCreate, ou
    // relancée via le bouton "Payer") juste avant ses 24h pouvait être annulée par cette tâche
    // pendant que le client était en train de payer. PaymentService::initiateForOrder() appelle
    // maintenant $order->touch() à chaque tentative, ce qui doit protéger la commande ici.
    public function test_an_order_actively_being_paid_survives_the_expiry_even_if_created_long_ago(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->makeStaleOrder($client); // created_at ET updated_at à -25h

        // fresh() d'abord : l'objet $order en mémoire garde encore son updated_at d'AVANT le
        // backdate SQL brut (posé après son dernier save() dans le helper) — sans recharger, la
        // comparaison "dirty" de touch() ne verrait aucun changement (même seconde que tout à
        // l'heure) et n'écrirait rien, un artefact de ce test seulement : en production, l'objet
        // vient d'être rechargé depuis la base juste avant (OrderController::initiate(), route
        // model binding), jamais du même appel que sa dernière écriture.
        $order->fresh()->touch();

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('pending_payment', $order->fresh()->status);
    }

    public function test_a_stale_pending_subscription_is_cancelled_and_restores_its_referral_credit(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
        $subscription = $this->makeStaleSubscription($prestataire);
        $subscription->applyReferralCredit(true);
        $this->backdateUpdatedAt($subscription, 25);

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertEquals(500, $prestataire->fresh()->referral_credit_balance);
    }

    // Corrigé suite à un 3e audit externe : une commande pending_payment supprimée (soft delete
    // admin) restait invisible à cette requête (contrainte par le global scope SoftDeletes), donc
    // jamais annulée — le crédit de parrainage consommé dessus restait bloqué pour toujours.
    public function test_a_stale_soft_deleted_order_is_still_cancelled_and_restores_its_referral_credit(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeStaleOrder($client);
        $order->applyReferralCredit(true);
        $this->backdateUpdatedAt($order, 25);
        // delete() (SoftDeletes) touche updated_at à "maintenant" comme tout save() : rebackdater
        // après, sinon la commande ne serait plus "stale" au sens de la requête.
        $order->delete();
        $this->backdateUpdatedAt($order, 25);

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertEquals(300, $client->fresh()->referral_credit_balance);
    }

    // Même correctif côté abonnement (audit externe — 3e audit).
    public function test_a_stale_soft_deleted_subscription_is_still_cancelled_and_restores_its_referral_credit(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
        $subscription = $this->makeStaleSubscription($prestataire);
        $subscription->applyReferralCredit(true);
        $this->backdateUpdatedAt($subscription, 25);
        $subscription->delete();
        $this->backdateUpdatedAt($subscription, 25);

        $this->artisan('payments:expire-stale-pending');

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertEquals(500, $prestataire->fresh()->referral_credit_balance);
    }

    // Corrigé suite à un 3e audit externe : ExpireStalePendingPayments charge les commandes stale
    // AVANT de verrouiller chacune — un touch() concurrent (nouvelle tentative de paiement) entre
    // cette lecture et le verrou pris dans refund() pouvait quand même faire annuler la commande.
    // Order::refund() accepte maintenant la borne de fraîcheur utilisée par la requête
    // ($mustBeStaleSince) et la revérifie sous verrou, sur la valeur FRAÎCHEMENT lue en base —
    // reproduit ici directement (la vraie fenêtre de course n'est pas simulable en mono-thread).
    public function test_refund_aborts_when_the_order_was_touched_after_the_staleness_cutoff(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
        $order = $this->makeStaleOrder($client); // updated_at à -25h
        $order->applyReferralCredit(true);
        $this->backdateUpdatedAt($order, 25);

        // Le paiement a touché la commande APRÈS que la borne ait été capturée (simule
        // PaymentService::initiateForOrder() s'exécutant entre la requête et le verrou).
        $cutoff = now()->subHours(24);
        $order->fresh()->touch();

        $refunded = $order->refund('Expirée automatiquement.', $cutoff);

        $this->assertFalse($refunded);
        $this->assertSame('pending_payment', $order->fresh()->status);
        // Le crédit reste consommé : la commande est toujours activement en cours.
        $this->assertEquals(0, $client->fresh()->referral_credit_balance);
    }

    public function test_marking_a_subscription_payment_failed_immediately_cancels_it_and_restores_credit(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referral_credit_balance' => 500]);
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
        $subscription->applyReferralCredit(true);

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $prestataire->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $subscription->fresh()->total_charged,
            'status' => 'pending',
            'type' => 'subscription',
        ]);

        $payment->markAsFailed('{"status":"declined"}');

        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertEquals(500, $prestataire->fresh()->referral_credit_balance);
    }
}
