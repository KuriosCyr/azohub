<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

// Corrigé suite à un 6e audit externe : les compteurs d'impressions et de clics publicitaires
// s'incrémentaient sans aucune limite ni dédoublonnage, contrairement aux vues de service
// (ServiceView) qui ont déjà cette protection — un rechargement de page, un robot ou un
// rafraîchissement automatique gonflait les statistiques sans limite.
class AdvertisementTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeAd(): Advertisement
    {
        return Advertisement::create([
            'title' => 'Pub test',
            'advertiser_name' => 'Annonceur',
            'placement' => 'home_banner',
            'image' => 'ads/test.jpg',
            'link_url' => 'https://example.com',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
            'impressions' => 0,
            'clicks' => 0,
        ]);
    }

    public function test_repeated_impressions_from_the_same_guest_within_an_hour_are_only_counted_once(): void
    {
        $ad = $this->makeAd();

        $ad->recordImpression();
        $ad->recordImpression();
        $ad->recordImpression();

        $this->assertEquals(1, $ad->fresh()->impressions);
    }

    public function test_repeated_clicks_from_the_same_guest_within_an_hour_are_only_counted_once(): void
    {
        $ad = $this->makeAd();

        $ad->recordClick();
        $ad->recordClick();

        $this->assertEquals(1, $ad->fresh()->clicks);
    }

    public function test_impressions_from_different_visitors_are_each_counted(): void
    {
        $ad = $this->makeAd();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first);
        $ad->recordImpression();

        $this->actingAs($second);
        $ad->recordImpression();

        $this->assertEquals(2, $ad->fresh()->impressions);
    }

    public function test_an_impression_is_counted_again_once_the_throttle_window_has_passed(): void
    {
        $ad = $this->makeAd();

        $ad->recordImpression();
        Cache::flush();
        $ad->recordImpression();

        $this->assertEquals(2, $ad->fresh()->impressions);
    }

    // Impressions et clics sont dédoublonnés indépendamment : voir un clic ne doit pas empêcher
    // de compter une impression distincte, et inversement.
    public function test_impressions_and_clicks_are_throttled_independently(): void
    {
        $ad = $this->makeAd();

        $ad->recordImpression();
        $ad->recordClick();

        $this->assertEquals(1, $ad->fresh()->impressions);
        $this->assertEquals(1, $ad->fresh()->clicks);
    }
}
