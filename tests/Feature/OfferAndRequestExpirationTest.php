<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\CustomOffer;
use App\Models\Order;
use App\Models\Payment;
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

    // Corrigé suite à un audit externe : si le paiement se confirme juste après que l'offre ait
    // expiré entre-temps (ExpireStaleOffers a tourné pendant que le client était sur la page
    // FedaPay), la commande s'active quand même (l'argent est réel) mais l'offre restait bloquée
    // sur "expirée" au lieu de refléter qu'elle avait bien été honorée.
    public function test_a_payment_confirmed_after_the_offer_expired_still_marks_it_accepted(): void
    {
        $client = User::factory()->create(['role' => 'client']);
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
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'custom_offer_id' => $offer->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 5,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'mtn_momo',
            'amount' => $order->amount,
            'status' => 'pending',
            'type' => 'order_payment',
        ])->markAsPaid('{"status":"approved"}');

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('accepted', $offer->fresh()->status);
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
