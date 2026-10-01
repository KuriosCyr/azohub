<?php

namespace Tests\Feature;

use App\Livewire\ProposalAccept;
use App\Models\Category;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

// Corrigé suite à un 8e audit externe : contrairement à une commande directe sur un service
// (Service::isOrderable()), ce parcours négocié ne vérifiait jamais que le prestataire était
// actif et non supprimé avant de créer la commande et d'initier le paiement — l'argent pouvait
// partir en escrow vers un compte bloqué qui ne livrerait jamais (désactivé), ou le client
// tombait sur une erreur 500 (supprimé).
class ProposalAcceptPrestataireStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeOpenProposal(): array
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Design', 'slug' => 'design-' . uniqid(), 'is_active' => true]);

        $serviceRequest = ServiceRequest::create([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'title' => 'Besoin d\'un logo',
            'description' => str_repeat('Détail de la demande. ', 5),
            'city' => 'Cotonou',
            'status' => 'open',
        ]);

        $proposal = Proposal::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $prestataire->id,
            'message' => 'Je peux le faire.',
            'proposed_price' => 5000,
            'delivery_time' => 3,
            'status' => 'pending',
        ]);

        return [$client, $prestataire, $serviceRequest, $proposal];
    }

    public function test_mount_succeeds_for_an_active_prestataire(): void
    {
        [$client, , $serviceRequest, $proposal] = $this->makeOpenProposal();

        Livewire::actingAs($client)
            ->test(ProposalAccept::class, ['serviceRequest' => $serviceRequest, 'proposal' => $proposal])
            ->assertOk();
    }

    public function test_mount_aborts_when_the_prestataire_is_deactivated(): void
    {
        [$client, $prestataire, $serviceRequest, $proposal] = $this->makeOpenProposal();
        $prestataire->update(['is_active' => false]);

        // Livewire::test() rend sinon la réponse d'erreur au lieu de laisser l'exception se
        // propager jusqu'à PHPUnit.
        $this->withoutExceptionHandling();
        $this->expectException(HttpException::class);

        Livewire::actingAs($client)
            ->test(ProposalAccept::class, ['serviceRequest' => $serviceRequest, 'proposal' => $proposal]);
    }

    public function test_mount_aborts_instead_of_crashing_when_the_prestataire_was_deleted(): void
    {
        [$client, $prestataire, $serviceRequest, $proposal] = $this->makeOpenProposal();
        $prestataire->delete();

        $this->withoutExceptionHandling();
        $this->expectException(HttpException::class);

        Livewire::actingAs($client)
            ->test(ProposalAccept::class, ['serviceRequest' => $serviceRequest, 'proposal' => $proposal]);
    }
}
