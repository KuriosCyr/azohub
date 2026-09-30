<?php

namespace Tests\Feature;

use App\Livewire\PrestataireStatistics;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

// Corrigé suite à un 5e audit externe : même schéma de débordement de date de fin de mois que
// StatsOverview (Carbon déborde par défaut en fin de mois — voir StatsOverviewRevenueTest pour la
// vérification factuelle du comportement de Carbon lui-même). Ce fichier vérifie que le correctif
// a bien été appliqué à CE composant, pas seulement au widget admin.
class PrestataireStatisticsTest extends TestCase
{
    use RefreshDatabase;

    // hasAccess de PrestataireStatistics vérifie in_array($plan?->slug, ['pro', 'premium']) : le
    // slug doit être exactement 'pro' (pas un suffixe unique comme dans d'autres tests de ce
    // repo) pour passer ce contrôle.
    private function makeProPrestataireWithExactSlug(): User
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $plan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'pro'],
            ['name' => 'Pro', 'price' => 3000, 'max_services' => 10, 'commission_rate' => 10, 'is_active' => true]
        );
        Subscription::create([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);

        return $prestataire;
    }

    // Le 31 mars, l'ancien calcul (subMonths($i) depuis "maintenant", jour variable) débordait sur
    // les mois plus courts — testé en réel pour StatsOverview (même défaut ici, même correctif :
    // partir du 1er du mois avant de soustraire). La fenêtre de 6 mois doit couvrir exactement
    // octobre 2025 à mars 2026, ni plus ni moins (sept. exclu, avr. jamais atteint).
    public function test_the_six_month_window_does_not_overflow_on_the_31st_of_march(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 31, 12, 0, 0));

        try {
            $prestataire = $this->makeProPrestataireWithExactSlug();

            Livewire::actingAs($prestataire)
                ->test(PrestataireStatistics::class)
                ->assertSeeInOrder(['Oct', 'Nov', 'Déc', 'Jan', 'Fév', 'Mar'])
                ->assertDontSee('Sep');
        } finally {
            Carbon::setTestNow();
        }
    }
}
