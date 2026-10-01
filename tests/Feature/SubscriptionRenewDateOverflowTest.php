<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// Corrigé suite à un 6e audit externe : Carbon (3.11, ce projet) déborde par défaut sur les mois
// plus courts — testé en réel, Carbon::parse('2026-01-31')->addMonth() donne '2026-03-03' au lieu
// du 28 février attendu. Un abonnement mensuel pris le 31 janvier se terminait donc le 3 mars
// (environ 32 jours) au lieu du 28 février (environ 28 jours) — même défaut que celui déjà
// corrigé côté StatsOverview/PrestataireStatistics (subMonth()), mais dans l'autre sens temporel.
class SubscriptionRenewDateOverflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeSubscription(): Subscription
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-' . uniqid(),
            'price' => 3000,
            'max_services' => 10,
            'commission_rate' => 10,
            'is_active' => true,
        ]);

        return Subscription::create([
            'user_id' => $prestataire->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now(),
            'status' => 'pending',
            'billing_period' => 'monthly',
            'auto_renew' => true,
        ]);
    }

    public function test_a_monthly_renewal_starting_on_the_31st_of_january_ends_on_the_28th_of_february(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 31, 10, 0, 0));

        try {
            $subscription = $this->makeSubscription();

            $subscription->renew();

            $this->assertSame('2026-02-28', $subscription->ends_at->toDateString());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_a_yearly_renewal_starting_on_the_29th_of_february_of_a_leap_year_ends_on_the_28th_of_february(): void
    {
        Carbon::setTestNow(Carbon::create(2028, 2, 29, 10, 0, 0));

        try {
            $subscription = $this->makeSubscription();
            $subscription->update(['billing_period' => 'yearly']);

            $subscription->renew();

            $this->assertSame('2029-02-28', $subscription->ends_at->toDateString());
        } finally {
            Carbon::setTestNow();
        }
    }

    // Un renouvellement anticipé reporte le temps restant d'un abonnement encore en cours — même
    // protection contre le débordement quand $carryOverFrom tombe sur un jour 29-31.
    // $carryOverFrom n'est utilisé comme base que s'il est dans le FUTUR par rapport à "maintenant"
    // (sinon l'abonnement remplacé est déjà expiré, rien à reporter) — "maintenant" est donc figé
    // avant cette date pour que renew() l'utilise bien comme base.
    public function test_renewing_early_with_a_carry_over_date_on_the_31st_does_not_overflow(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 10, 0, 0));

        try {
            $subscription = $this->makeSubscription();

            $subscription->renew(Carbon::create(2026, 1, 31, 0, 0, 0));

            $this->assertSame('2026-02-28', $subscription->ends_at->toDateString());
        } finally {
            Carbon::setTestNow();
        }
    }
}
