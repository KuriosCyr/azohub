<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Couvre User::maybeRewardReferrer(), déclenché par Payment::activatePaidOrder() (première
// commande payée d'un client) et Order::releasePayment() (première commande livrée et payée
// d'un prestataire) — le parrain touche 100 FCFA de crédit non retirable, une seule fois par
// filleul, dans la limite de User::MAX_REWARDED_REFERRALS parrainages récompensés.
class ReferralRewardTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $client, User $prestataire, array $overrides = []): Order
    {
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

    public function test_a_referred_clients_first_paid_order_credits_the_referrer_once(): void
    {
        $referrer = User::factory()->create(['role' => 'client']);
        $referee = User::factory()->create(['role' => 'client', 'referred_by' => $referrer->id]);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = $this->makeOrder($referee, $prestataire);
        $this->makePayment($order)->markAsPaid('{"status":"approved"}');

        $referrer->refresh();
        $referee->refresh();
        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $referrer->referral_credit_balance);
        $this->assertTrue($referee->referral_reward_granted);

        // Une deuxième commande payée par le même filleul ne récompense pas une deuxième fois.
        $order2 = $this->makeOrder($referee, $prestataire);
        $this->makePayment($order2)->markAsPaid('{"status":"approved"}');

        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $referrer->fresh()->referral_credit_balance);
    }

    public function test_a_referred_prestataires_first_released_order_credits_the_referrer_once(): void
    {
        $referrer = User::factory()->create(['role' => 'prestataire']);
        $referee = User::factory()->create(['role' => 'prestataire', 'referred_by' => $referrer->id]);
        $client = User::factory()->create(['role' => 'client']);

        $order = $this->makeOrder($client, $referee, ['status' => 'delivered', 'payment_status' => 'held']);
        $order->releasePayment();

        $referrer->refresh();
        $referee->refresh();
        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $referrer->referral_credit_balance);
        $this->assertTrue($referee->referral_reward_granted);

        $order2 = $this->makeOrder($client, $referee, ['status' => 'delivered', 'payment_status' => 'held']);
        $order2->releasePayment();

        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $referrer->fresh()->referral_credit_balance);
    }

    public function test_referrals_beyond_the_cap_are_marked_granted_but_do_not_add_credit(): void
    {
        $referrer = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 0]);

        // Simule que le parrain a déjà atteint le plafond de filleuls récompensés.
        User::factory()->count(User::MAX_REWARDED_REFERRALS)->create([
            'role' => 'client',
            'referred_by' => $referrer->id,
            'referral_reward_granted' => true,
        ]);

        $referrer->update(['referral_credit_balance' => User::REFERRAL_REWARD_AMOUNT * User::MAX_REWARDED_REFERRALS]);

        $overCapReferee = User::factory()->create(['role' => 'client', 'referred_by' => $referrer->id]);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = $this->makeOrder($overCapReferee, $prestataire);
        $this->makePayment($order)->markAsPaid('{"status":"approved"}');

        $overCapReferee->refresh();
        $this->assertTrue($overCapReferee->referral_reward_granted);
        $this->assertEquals(
            User::REFERRAL_REWARD_AMOUNT * User::MAX_REWARDED_REFERRALS,
            $referrer->fresh()->referral_credit_balance
        );
    }

    public function test_a_user_with_no_referrer_triggers_nothing(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referred_by' => null]);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = $this->makeOrder($client, $prestataire);
        $this->makePayment($order)->markAsPaid('{"status":"approved"}');

        $this->assertEquals(0, $client->fresh()->referral_credit_balance);
    }
}
