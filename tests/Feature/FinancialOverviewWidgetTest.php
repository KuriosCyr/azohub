<?php

namespace Tests\Feature;

use App\Filament\Widgets\FinancialOverview;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Suggestion d'un audit externe, implémentée : un tableau de bord financier admin manquait pour
// voir d'un coup d'œil l'escrow en cours, les remboursements à traiter et les retraits à payer.
// Corrigé suite à un second audit externe : "Remboursements à traiter" comptait orders.amount
// (ignore frais/réductions, compte un remboursement PARTIEL comme total, rate les paiements
// orphelins dont seul le paiement — pas la commande — passe en refund_pending) — basé maintenant
// sur payments.refund_amount_due, le montant exact dû au client.
class FinancialOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(Order $order, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'order_id' => $order->id,
            'user_id' => $order->client_id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'success',
            'type' => 'order_payment',
        ], $overrides));
    }

    public function test_widget_shows_escrow_refunds_withdrawals_and_wallet_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire', 'wallet_balance' => 7000]);

        // Escrow : 9000 dus au prestataire, bloqués.
        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);

        // Remboursement complet à traiter : 5000.
        $refundOrder = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 5000,
            'commission' => 500,
            'prestataire_amount' => 4500,
            'delivery_time' => 3,
            'status' => 'cancelled',
            'payment_status' => 'refund_pending',
        ]);
        $this->makePayment($refundOrder, ['amount' => 5000, 'status' => 'refund_pending', 'refund_amount_due' => 5000]);

        // Retrait en attente : 3000.
        WithdrawalRequest::create([
            'prestataire_id' => $prestataire->id,
            'amount' => 3000,
            'payment_method' => 'mtn_momo',
            'phone_number' => '97000000',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(FinancialOverview::class)
            ->assertSee('9 000 FCFA')   // escrow
            ->assertSee('5 000 FCFA')   // remboursement
            ->assertSee('3 000 FCFA')   // retrait
            ->assertSee('7 000 FCFA');  // solde portefeuille prestataire
    }

    public function test_a_partial_refund_only_counts_the_client_portion_not_the_full_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'completed',
            'payment_status' => 'refund_pending',
        ]);

        // Le client a payé 10000, mais seuls 4000 lui reviennent (remboursement partiel) — le
        // reste est déjà chez le prestataire.
        $this->makePayment($order, ['amount' => 10000, 'status' => 'refund_pending', 'refund_amount_due' => 4000]);

        Livewire::actingAs($admin)
            ->test(FinancialOverview::class)
            ->assertSee('4 000 FCFA')
            ->assertDontSee('10 000 FCFA');
    }

    public function test_an_orphaned_payment_shows_even_when_its_order_is_not_marked_refund_pending(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        // La commande elle-même n'est jamais passée en refund_pending (déjà 'cancelled' avant
        // même que ce paiement en double n'arrive) — seul le paiement l'est.
        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 6000,
            'commission' => 600,
            'prestataire_amount' => 5400,
            'delivery_time' => 3,
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);
        $this->makePayment($order, ['amount' => 6000, 'status' => 'refund_pending', 'refund_amount_due' => 6000]);

        Livewire::actingAs($admin)
            ->test(FinancialOverview::class)
            ->assertSee('6 000 FCFA');
    }
}
