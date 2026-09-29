<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Suggestion d'un audit externe, implémentée avec prudence (argent réel, API tierce) : les
// retraits peuvent maintenant être payés automatiquement via l'API Payout de FedaPay, en plus du
// bouton manuel existant conservé comme solution de repli. Teste uniquement la logique métier
// (WithdrawalRequest, PaymentService::processPayoutUpdate) — jamais l'appel réel à l'API FedaPay
// (initiatePayout), qui reste vérifié manuellement en sandbox, pas dans la suite automatisée.
class FedapayPayoutTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingWithdrawal(): WithdrawalRequest
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 0]);

        return WithdrawalRequest::create([
            'prestataire_id' => $prestataire->id,
            'amount' => 5000,
            'payment_method' => 'mtn_momo',
            'phone_number' => '97000000',
            'status' => 'pending',
        ]);
    }

    public function test_a_fresh_withdrawal_can_be_retried_via_fedapay(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $this->assertTrue($withdrawal->canRetryFedapayPayout());
    }

    public function test_marking_a_payout_sent_blocks_a_second_attempt(): void
    {
        $withdrawal = $this->makePendingWithdrawal();

        $withdrawal->markFedapayPayoutSent('payout_123');

        $this->assertFalse($withdrawal->fresh()->canRetryFedapayPayout());
        $this->assertSame('pending', $withdrawal->fresh()->fedapay_status);
        // Le statut métier ne change pas tant que la confirmation n'est pas arrivée.
        $this->assertSame('pending', $withdrawal->fresh()->status);
    }

    public function test_confirming_a_payout_marks_the_withdrawal_paid(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->markFedapayPayoutSent('payout_123');

        $withdrawal->confirmFedapayPayout();

        $withdrawal->refresh();
        $this->assertSame('paid', $withdrawal->status);
        $this->assertSame('sent', $withdrawal->fedapay_status);
        // Le solde n'est PAS recrédité : il a déjà été débité à la demande, et reste débité,
        // l'argent étant maintenant bien arrivé chez le prestataire via FedaPay.
        $this->assertEquals(0, $withdrawal->prestataire->wallet_balance);
    }

    public function test_a_failed_payout_credits_the_wallet_back_and_allows_a_retry(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->markFedapayPayoutSent('payout_123');

        $withdrawal->failFedapayPayout('Numéro invalide');

        $withdrawal->refresh();
        $this->assertSame('pending', $withdrawal->status, 'Un échec ne doit pas rejeter la demande, juste permettre un nouvel essai.');
        $this->assertSame('failed', $withdrawal->fedapay_status);
        $this->assertNull($withdrawal->fedapay_payout_id);
        $this->assertTrue($withdrawal->canRetryFedapayPayout());
        $this->assertEquals(5000, $withdrawal->prestataire->fresh()->wallet_balance);
    }

    public function test_process_payout_update_routes_sent_and_failed_correctly(): void
    {
        $service = app(PaymentService::class);

        $sentWithdrawal = $this->makePendingWithdrawal();
        $sentWithdrawal->markFedapayPayoutSent('payout_sent');
        $service->processPayoutUpdate('payout_sent', 'sent', null);
        $this->assertSame('paid', $sentWithdrawal->fresh()->status);

        $failedWithdrawal = $this->makePendingWithdrawal();
        $failedWithdrawal->markFedapayPayoutSent('payout_failed');
        $service->processPayoutUpdate('payout_failed', 'failed', 'Solde FedaPay insuffisant');
        $this->assertSame('pending', $failedWithdrawal->fresh()->status);
        $this->assertSame('failed', $failedWithdrawal->fresh()->fedapay_status);

        // Un statut encore intermédiaire ne doit rien changer.
        $pendingWithdrawal = $this->makePendingWithdrawal();
        $pendingWithdrawal->markFedapayPayoutSent('payout_still_pending');
        $service->processPayoutUpdate('payout_still_pending', 'pending', null);
        $this->assertSame('pending', $pendingWithdrawal->fresh()->status);
        $this->assertSame('pending', $pendingWithdrawal->fresh()->fedapay_status);
    }

    public function test_an_unknown_payout_id_is_ignored_without_error(): void
    {
        $service = app(PaymentService::class);

        $service->processPayoutUpdate('payout_inconnu', 'sent', null);

        $this->assertTrue(true); // N'a pas levé d'exception.
    }
}
