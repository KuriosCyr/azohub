<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\CustomOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\CustomOfferExpired;
use App\Notifications\ServiceRequestExpired;
use App\Notifications\ServiceRequestNoProposals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// Suggestion d'un audit externe, implémentée : ni les offres personnalisées ni les demandes de
// service n'expiraient jamais réellement (service_requests.expires_at existait déjà en base,
// éditable depuis l'admin, mais jamais renseigné ni vérifié nulle part).
class OfferAndRequestExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pending_offer_past_its_expiry_is_expired_and_prestataire_notified(): void
    {
        Notification::fake();

        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $conversation = Conversation::create(['client_id' => $client->id, 'prestataire_id' => $prestataire->id]);

        $offer = CustomOffer::create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'title' => 'Offre test',
            'description' => 'Description de test.',
            'price' => 10000,
            'delivery_days' => 5,
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('offers:expire-stale')->assertExitCode(0);

        $this->assertSame('expired', $offer->fresh()->status);
        Notification::assertSentTo($prestataire, CustomOfferExpired::class);
    }

    public function test_an_offer_not_yet_expired_is_left_untouched(): void
    {
        Notification::fake();

        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $conversation = Conversation::create(['client_id' => $client->id, 'prestataire_id' => $prestataire->id]);

        $offer = CustomOffer::create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'title' => 'Offre test',
            'description' => 'Description de test.',
            'price' => 10000,
            'delivery_days' => 5,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);

        $this->artisan('offers:expire-stale')->assertExitCode(0);

        $this->assertSame('pending', $offer->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_an_open_service_request_past_its_expiry_is_expired_and_client_notified(): void
    {
        Notification::fake();

        $client = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'test-' . uniqid(), 'is_active' => true]);

        $serviceRequest = ServiceRequest::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'title' => 'Demande test',
            'description' => 'Description de test.',
            'city' => 'Cotonou',
            'status' => 'open',
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('requests:expire-stale')->assertExitCode(0);

        $this->assertSame('expired', $serviceRequest->fresh()->status);
        Notification::assertSentTo($client, ServiceRequestExpired::class);
    }

    public function test_a_service_request_with_zero_proposals_is_reminded_once_after_the_delay(): void
    {
        Notification::fake();

        $client = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'test-' . uniqid(), 'is_active' => true]);

        $serviceRequest = ServiceRequest::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'title' => 'Demande sans proposition',
            'description' => 'Description de test.',
            'city' => 'Cotonou',
            'status' => 'open',
            'proposals_count' => 0,
        ]);
        // created_at n'est pas mass-assignable (create() l'ignore silencieusement) : affectation
        // directe pour simuler une demande vieille de 4 jours.
        $serviceRequest->created_at = now()->subDays(4);
        $serviceRequest->save();

        $this->artisan('requests:remind-stale')->assertExitCode(0);

        $serviceRequest->refresh();
        $this->assertNotNull($serviceRequest->stale_reminded_at);
        Notification::assertSentTo($client, ServiceRequestNoProposals::class, 1);

        // Un second passage ne relance pas une seconde fois.
        $this->artisan('requests:remind-stale')->assertExitCode(0);
        Notification::assertSentTo($client, ServiceRequestNoProposals::class, 1);
    }

    public function test_a_recent_service_request_with_no_proposals_is_not_reminded_yet(): void
    {
        Notification::fake();

        $client = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'test-' . uniqid(), 'is_active' => true]);

        ServiceRequest::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'title' => 'Demande recente',
            'description' => 'Description de test.',
            'city' => 'Cotonou',
            'status' => 'open',
            'proposals_count' => 0,
            'created_at' => now()->subHours(2),
        ]);

        $this->artisan('requests:remind-stale')->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
