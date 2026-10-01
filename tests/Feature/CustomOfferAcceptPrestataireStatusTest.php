<?php

namespace Tests\Feature;

use App\Livewire\CustomOfferAccept;
use App\Models\Conversation;
use App\Models\CustomOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

// Même correctif que ProposalAcceptPrestataireStatusTest, côté offre personnalisée (audit
// externe — 8e audit).
class CustomOfferAcceptPrestataireStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingOffer(): array
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $conversation = Conversation::create([
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
        ]);

        $offer = CustomOffer::create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'title' => 'Offre sur mesure',
            'description' => 'Détail de l\'offre.',
            'price' => 8000,
            'delivery_days' => 5,
            'revisions_included' => 1,
            'status' => 'pending',
        ]);

        return [$client, $prestataire, $offer];
    }

    public function test_mount_succeeds_for_an_active_prestataire(): void
    {
        [$client, , $offer] = $this->makePendingOffer();

        Livewire::actingAs($client)
            ->test(CustomOfferAccept::class, ['offer' => $offer])
            ->assertOk();
    }

    public function test_mount_aborts_when_the_prestataire_is_deactivated(): void
    {
        [$client, $prestataire, $offer] = $this->makePendingOffer();
        $prestataire->update(['is_active' => false]);

        $this->withoutExceptionHandling();
        $this->expectException(HttpException::class);

        Livewire::actingAs($client)
            ->test(CustomOfferAccept::class, ['offer' => $offer]);
    }

    public function test_mount_aborts_instead_of_crashing_when_the_prestataire_was_deleted(): void
    {
        [$client, $prestataire, $offer] = $this->makePendingOffer();
        $prestataire->delete();

        $this->withoutExceptionHandling();
        $this->expectException(HttpException::class);

        Livewire::actingAs($client)
            ->test(CustomOfferAccept::class, ['offer' => $offer]);
    }
}
