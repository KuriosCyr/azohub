<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProfileView;
use App\Models\Service;
use App\Models\ServiceView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Corrigé suite à un 8e audit externe : l'effet inverse du correctif du tour précédent (IP ->
// session pour dédoublonner les invités) — un robot/crawler sans cookies obtient une NOUVELLE
// session à chaque requête, donc chaque visite était comptée séparément. Sans cookie de session
// entrant, la vue n'est plus comptée du tout. Passe par de vraies requêtes HTTP (pas
// Livewire::test()) : Livewire::test() monte le composant via sa propre requête simulée,
// déconnectée du jar de cookies de test (withCookie()) et de l'instance request() déjà liée au
// conteneur — seule une vraie requête HTTP applique les cookies de test de façon fiable ici.
class GuestViewDedupTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): Service
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Design', 'slug' => 'design-' . uniqid(), 'is_active' => true]);

        return Service::create([
            'user_id' => $prestataire->id,
            'category_id' => $category->id,
            'title' => 'Service test',
            'slug' => 'service-test-' . uniqid(),
            'description' => str_repeat('Description de test suffisamment longue. ', 2),
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_a_guest_without_an_existing_session_cookie_does_not_count_as_a_service_view(): void
    {
        $service = $this->makeService();

        $this->get(route('services.show', $service));

        $this->assertSame(0, ServiceView::where('service_id', $service->id)->count());
    }

    public function test_a_returning_guest_counts_as_a_service_view(): void
    {
        $service = $this->makeService();

        $this->withCookie(config('session.cookie'), 'fake-existing-session-id')
            ->get(route('services.show', $service));

        $this->assertSame(1, ServiceView::where('service_id', $service->id)->count());
    }

    public function test_a_guest_without_an_existing_session_cookie_does_not_count_as_a_profile_view(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'is_active' => true]);

        $this->get(route('prestataire.profile', $prestataire->slug));

        $this->assertSame(0, ProfileView::where('prestataire_id', $prestataire->id)->count());
    }

    public function test_a_returning_guest_counts_as_a_profile_view(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire', 'is_active' => true]);

        $this->withCookie(config('session.cookie'), 'fake-existing-session-id')
            ->get(route('prestataire.profile', $prestataire->slug));

        $this->assertSame(1, ProfileView::where('prestataire_id', $prestataire->id)->count());
    }
}
