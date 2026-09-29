<?php

namespace Tests\Feature;

use App\Livewire\PrestataireWallet;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Suite à un audit externe : seule la colonne wallet_balance était incrémentée/décrémentée,
// sans aucun historique pour reconstituer un écart en cas de litige comptable. User::
// creditWallet()/debitWallet() est maintenant le seul point d'écriture, et journalise chaque
// mouvement dans wallet_transactions.
class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_crediting_the_wallet_logs_a_transaction_with_the_resulting_balance(): void
    {
        $user = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 1000]);

        $user->creditWallet(500, 'Test de crédit');

        $user->refresh();
        $this->assertEquals(1500, $user->wallet_balance);

        $transaction = WalletTransaction::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($transaction);
        $this->assertSame('credit', $transaction->type);
        $this->assertEquals(500, $transaction->amount);
        $this->assertEquals(1500, $transaction->balance_after);
        $this->assertSame('Test de crédit', $transaction->reason);
    }

    public function test_debiting_the_wallet_logs_a_transaction_and_fails_gracefully_if_insufficient(): void
    {
        $user = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 1000]);

        $ok = $user->debitWallet(400, 'Test de débit');
        $this->assertTrue($ok);
        $user->refresh();
        $this->assertEquals(600, $user->wallet_balance);

        $transaction = WalletTransaction::where('user_id', $user->id)->latest()->first();
        $this->assertSame('debit', $transaction->type);
        $this->assertEquals(600, $transaction->balance_after);

        // Solde insuffisant : refusé, rien de journalisé en plus.
        $countBefore = WalletTransaction::where('user_id', $user->id)->count();
        $failed = $user->debitWallet(10000, 'Trop gros retrait');
        $this->assertFalse($failed);
        $this->assertEquals(600, $user->fresh()->wallet_balance);
        $this->assertSame($countBefore, WalletTransaction::where('user_id', $user->id)->count());
    }

    public function test_releasing_payment_on_an_order_logs_a_credit_with_the_order_as_source(): void
    {
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
        ]);

        $order->releasePayment();

        $transaction = WalletTransaction::where('user_id', $prestataire->id)->latest()->first();
        $this->assertEquals(9000, $transaction->amount);
        $this->assertEquals(9000, $transaction->balance_after);
        $this->assertSame(Order::class, $transaction->source_type);
        $this->assertSame($order->id, $transaction->source_id);
    }

    public function test_rejecting_a_withdrawal_logs_a_credit_referencing_it(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 0]);
        $admin = User::factory()->create(['role' => 'admin']);

        $withdrawal = WithdrawalRequest::create([
            'prestataire_id' => $prestataire->id,
            'amount' => 5000,
            'payment_method' => 'mtn_momo',
            'phone_number' => '97000000',
            'status' => 'pending',
        ]);

        $withdrawal->reject($admin->id, 'Numéro invalide.');

        $transaction = WalletTransaction::where('user_id', $prestataire->id)->latest()->first();
        $this->assertEquals(5000, $transaction->amount);
        $this->assertSame(WithdrawalRequest::class, $transaction->source_type);
        $this->assertSame($withdrawal->id, $transaction->source_id);
    }

    public function test_requesting_a_withdrawal_debits_the_wallet_and_references_the_request(): void
    {
        $prestataire = User::factory()->create([
            'role' => 'prestataire',
            'wallet_balance' => 10000,
            'identity_verified' => true,
        ]);

        Livewire::actingAs($prestataire)
            ->test(PrestataireWallet::class)
            ->set('amount', 5000)
            ->set('paymentMethod', 'mtn_momo')
            ->set('phoneNumber', '0197000000')
            ->call('requestWithdrawal');

        $prestataire->refresh();
        $this->assertEquals(5000, $prestataire->wallet_balance);

        $withdrawal = WithdrawalRequest::where('prestataire_id', $prestataire->id)->latest()->first();
        $this->assertNotNull($withdrawal);
        $this->assertEquals(5000, $withdrawal->amount);

        $transaction = WalletTransaction::where('user_id', $prestataire->id)->latest()->first();
        $this->assertSame('debit', $transaction->type);
        $this->assertEquals(5000, $transaction->balance_after);
        $this->assertSame(WithdrawalRequest::class, $transaction->source_type);
        $this->assertSame($withdrawal->id, $transaction->source_id);
    }
}
