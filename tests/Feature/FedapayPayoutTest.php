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

    // Bug corrigé suite à un audit externe : failFedapayPayout() recréditait le portefeuille,
    // alors que le solde n'avait jamais bougé (débité une seule fois à la création de la demande,
    // jamais touché par un essai FedaPay raté). Ce recrédit permettait un double paiement : soit
    // en relançant "Payer via FedaPay" (le prestataire recevait l'argent en plus du recrédit),
    // soit via "Rejeter" juste après (qui créditait une DEUXIÈME fois).
    public function test_a_failed_payout_does_not_touch_the_wallet_and_allows_a_retry(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->markFedapayPayoutSent('payout_123');

        $withdrawal->failFedapayPayout('Numéro invalide');

        $withdrawal->refresh();
        $this->assertSame('pending', $withdrawal->status, 'Un échec ne doit pas rejeter la demande, juste permettre un nouvel essai.');
        $this->assertSame('failed', $withdrawal->fedapay_status);
        $this->assertNull($withdrawal->fedapay_payout_id);
        $this->assertTrue($withdrawal->canRetryFedapayPayout());
        $this->assertEquals(0, $withdrawal->prestataire->fresh()->wallet_balance);
    }

    public function test_rejecting_after_a_failed_payout_credits_the_wallet_only_once(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->markFedapayPayoutSent('payout_123');
        $withdrawal->failFedapayPayout('Numéro invalide');
        $admin = User::factory()->create(['role' => 'admin']);

        $withdrawal->reject($admin->id, 'Numéro toujours invalide après vérification.');

        $this->assertEquals(5000, $withdrawal->prestataire->fresh()->wallet_balance);
        $this->assertSame('rejected', $withdrawal->fresh()->status);
    }

    // Fenêtre de course corrigée : PaymentService::initiatePayout() marque la demande
    // 'initiating' (sous verrou) avant le moindre appel réseau, pour qu'un second clic pendant
    // l'appel à FedaPay::Payout::create() ne puisse pas aussi déclencher un vrai virement.
    public function test_an_initiating_payout_blocks_a_concurrent_retry(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->update(['fedapay_status' => 'initiating']);

        $this->assertFalse($withdrawal->fresh()->canRetryFedapayPayout());
    }

    // Cas ambigu : l'identifiant FedaPay a été obtenu (donc persisté) mais l'envoi n'a jamais pu
    // être confirmé par notre code — ni le webhook ni un admin n'ont encore tranché. Aucune des
    // 3 actions normales ne doit être possible tant que ce n'est pas résolu (elles exigent toutes
    // fedapay_payout_id === null), pour ne jamais risquer un double envoi.
    public function test_an_ambiguous_payout_blocks_retry_until_manually_cleared(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->markFedapayPayoutSent('payout_ambigu');

        $this->assertFalse($withdrawal->fresh()->canRetryFedapayPayout());

        $withdrawal->clearAmbiguousFedapayAttempt();

        $this->assertNull($withdrawal->fresh()->fedapay_payout_id);
        $this->assertTrue($withdrawal->fresh()->canRetryFedapayPayout());
    }

    public function test_clearing_an_already_confirmed_payout_does_nothing(): void
    {
        $withdrawal = $this->makePendingWithdrawal();
        $withdrawal->markFedapayPayoutSent('payout_123');
        $withdrawal->confirmFedapayPayout();

        $withdrawal->clearAmbiguousFedapayAttempt();

        $this->assertSame('paid', $withdrawal->fresh()->status);
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
