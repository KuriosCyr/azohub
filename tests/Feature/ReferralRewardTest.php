<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Couvre User::maybeRewardReferrer(), déclenché uniquement par User::approveIdentityVerification()
// — jamais par une commande. Un filleul qui reste client ne rapporte rien (choix produit :
// simplifie la mécanique, et corrige au passage une fraude possible identifiée en audit externe
// où de fausses commandes remboursées auraient pu générer du crédit indéfiniment). Seul un
// filleul devenu prestataire, dont l'identité est approuvée par un admin, rapporte 100 FCFA à son
// parrain (client ou prestataire), une seule fois, dans la limite de MAX_REWARDED_REFERRALS.
class ReferralRewardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_referred_prestataires_identity_approval_credits_the_referrer_once(): void
    {
        $referrer = User::factory()->create(['role' => 'client']);
        $referee = User::factory()->create(['role' => 'prestataire', 'referred_by' => $referrer->id]);

        $referee->approveIdentityVerification();

        $referrer->refresh();
        $referee->refresh();
        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $referrer->referral_credit_balance);
        $this->assertTrue($referee->referral_reward_granted);
        $this->assertTrue($referee->identity_verified);

        // Une deuxième approbation (ex. re-soumission après un renouvellement de document) ne
        // récompense pas une deuxième fois.
        $referee->approveIdentityVerification();

        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $referrer->fresh()->referral_credit_balance);
    }

    public function test_a_referred_client_never_rewards_its_referrer(): void
    {
        $referrer = User::factory()->create(['role' => 'prestataire']);
        $referee = User::factory()->create(['role' => 'client', 'referred_by' => $referrer->id]);

        $client = $referee;
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
        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'pending',
            'type' => 'order_payment',
        ])->markAsPaid('{"status":"approved"}');

        // Un client n'a de toute façon aucun moyen de vérifier son identité (réservé aux
        // prestataires), mais même en le forçant en base directement, rien ne se déclenche.
        $referee->forceFill(['identity_verified' => true])->save();
        $referee->maybeRewardReferrer();

        $this->assertEquals(0, $referrer->fresh()->referral_credit_balance);
        $this->assertFalse($referee->fresh()->referral_reward_granted);
    }

    public function test_referrals_beyond_the_cap_are_marked_granted_but_do_not_add_credit(): void
    {
        $referrer = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 0]);

        // Simule que le parrain a déjà atteint le plafond de filleuls récompensés.
        User::factory()->count(User::MAX_REWARDED_REFERRALS)->create([
            'role' => 'prestataire',
            'referred_by' => $referrer->id,
            'referral_reward_granted' => true,
        ]);

        $referrer->update(['referral_credit_balance' => User::REFERRAL_REWARD_AMOUNT * User::MAX_REWARDED_REFERRALS]);

        $overCapReferee = User::factory()->create(['role' => 'prestataire', 'referred_by' => $referrer->id]);

        $overCapReferee->approveIdentityVerification();

        $overCapReferee->refresh();
        $this->assertTrue($overCapReferee->referral_reward_granted);
        $this->assertEquals(
            User::REFERRAL_REWARD_AMOUNT * User::MAX_REWARDED_REFERRALS,
            $referrer->fresh()->referral_credit_balance
        );
    }

    public function test_a_user_with_no_referrer_triggers_nothing(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'referred_by' => null]);

        $prestataire->approveIdentityVerification();

        $this->assertEquals(0, $prestataire->fresh()->referral_credit_balance);
    }

    public function test_rejecting_identity_verification_grants_nothing(): void
    {
        $referrer = User::factory()->create(['role' => 'client']);
        $referee = User::factory()->create(['role' => 'prestataire', 'referred_by' => $referrer->id]);

        $referee->rejectIdentityVerification('Document illisible.');

        $this->assertEquals(0, $referrer->fresh()->referral_credit_balance);
        $this->assertFalse($referee->fresh()->referral_reward_granted);
    }
}
