<?php

namespace Tests\Feature;

use App\Filament\Widgets\FinancialOverview;
use App\Models\Order;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Suggestion d'un audit externe, implémentée : un tableau de bord financier admin manquait pour
// voir d'un coup d'œil l'escrow en cours, les remboursements à traiter et les retraits à payer.
class FinancialOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

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

        // Remboursement à traiter : 5000.
        Order::create([
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
}
