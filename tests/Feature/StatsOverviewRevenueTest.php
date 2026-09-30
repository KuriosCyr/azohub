<?php

namespace Tests\Feature;

use App\Filament\Widgets\StatsOverview;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Corrigé suite à un audit externe : "Bénéfices Azohub" sommait commission + client_fee sans
// soustraire le crédit de parrainage ou le code promo consommé — surestimant le revenu réel.
// prestataire_amount n'étant jamais réduit par une remise, ce qu'Azohub garde vraiment est bien
// commission + frais - réductions, pas commission + frais seuls.
class StatsOverviewRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_subtracts_referral_credit_and_promo_discount(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        // Marge brute (commission + client_fee) = 1500, réduite de 800 (300 crédit + 500 promo)
        // : la marge réelle gardée par Azohub est donc 700, pas 1500.
        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'client_fee' => 500,
            'referral_credit_applied' => 300,
            'promo_discount_applied' => 500,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);

        Livewire::actingAs($admin)
            ->test(StatsOverview::class)
            ->assertSee('700 FCFA')
            ->assertDontSee('1 500 FCFA');
    }
}
