<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ReferralCreditTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Corrige un point signalé par un audit externe : contrairement au portefeuille réel
// (wallet_transactions), le crédit de parrainage n'était ni journalisé ni visible dans l'admin.
class ReferralCreditLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_earning_a_referral_reward_logs_a_transaction(): void
    {
        $referrer = User::factory()->create(['role' => 'client']);
        $referee = User::factory()->create(['role' => 'prestataire', 'referred_by' => $referrer->id]);

        $referee->approveIdentityVerification();

        $transaction = ReferralCreditTransaction::where('user_id', $referrer->id)->latest()->first();
        $this->assertNotNull($transaction);
        $this->assertSame('earned', $transaction->type);
        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $transaction->amount);
        $this->assertEquals(User::REFERRAL_REWARD_AMOUNT, $transaction->balance_after);
        $this->assertSame(User::class, $transaction->source_type);
        $this->assertSame($referee->id, $transaction->source_id);
    }

    public function test_redeeming_credit_on_an_order_logs_a_transaction_referencing_it(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
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
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        $order->applyReferralCredit(true);

        $transaction = ReferralCreditTransaction::where('user_id', $client->id)->latest()->first();
        $this->assertSame('redeemed', $transaction->type);
        $this->assertEquals(300, $transaction->amount);
        $this->assertEquals(0, $transaction->balance_after);
        $this->assertSame(Order::class, $transaction->source_type);
        $this->assertSame($order->id, $transaction->source_id);
    }

    public function test_refunding_credit_on_a_cancelled_order_logs_a_transaction(): void
    {
        $client = User::factory()->create(['role' => 'client', 'referral_credit_balance' => 300]);
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
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);
        $order->applyReferralCredit(true);

        $order->refund('Le client a changé d\'avis.');

        $transaction = ReferralCreditTransaction::where('user_id', $client->id)->latest()->first();
        $this->assertSame('refunded', $transaction->type);
        $this->assertEquals(300, $transaction->amount);
        $this->assertEquals(300, $transaction->balance_after);
        $this->assertSame(Order::class, $transaction->source_type);
        $this->assertSame($order->id, $transaction->source_id);
    }
}
