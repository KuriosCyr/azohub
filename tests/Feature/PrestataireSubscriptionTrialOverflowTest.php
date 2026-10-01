<?php

namespace Tests\Feature;

use App\Livewire\PrestataireSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

// Corrigé suite à un 7e audit externe : le mois d'essai gratuit utilisait encore now()->addMonth()
// (débordement possible en fin de mois), incohérent avec Subscription::renew() qui, lui, ne
// déborde plus depuis le 6e audit (addMonthNoOverflow()).
class PrestataireSubscriptionTrialOverflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_trial_started_on_the_31st_of_january_ends_on_the_28th_of_february(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 31, 10, 0, 0));

        try {
            $prestataire = User::factory()->create(['role' => 'prestataire']);
            $plan = SubscriptionPlan::create([
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 3000,
                'max_services' => 10,
                'commission_rate' => 10,
                'is_active' => true,
            ]);

            Livewire::actingAs($prestataire)
                ->test(PrestataireSubscription::class)
                ->call('choosePlan', $plan->id);

            $trial = $prestataire->subscriptions()->where('is_trial', true)->first();

            $this->assertNotNull($trial);
            $this->assertSame('2026-02-28', $trial->ends_at->toDateString());
        } finally {
            Carbon::setTestNow();
        }
    }
}
