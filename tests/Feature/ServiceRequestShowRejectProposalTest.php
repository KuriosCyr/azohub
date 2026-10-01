<?php

namespace Tests\Feature;

use App\Livewire\ServiceRequestShow;
use App\Models\Category;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

// Corrigé suite à un 6e audit externe : ProposalAccept::confirm() crée une commande
// pending_payment pour une proposition SANS jamais changer son statut (reste 'pending' tant que
// le paiement n'est pas confirmé) — rien n'empêchait donc de refuser cette même proposition
// pendant qu'un paiement était déjà en cours. Si le client payait quand même,
// Payment::finalizeNegotiatedOrder() n'acceptant que les propositions encore 'pending' (pas
// 'rejected'), la commande s'activait mais la demande restait 'open' à tort.
class ServiceRequestShowRejectProposalTest extends TestCase
{
    use RefreshDatabase;

    private function makeServiceRequestWithProposal(): array
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

        return [$serviceRequest, $proposal, $client, $prestataire];
    }

    public function test_the_client_can_reject_a_proposal_with_no_payment_in_progress(): void
    {
        Notification::fake();
        [$serviceRequest, $proposal, $client] = $this->makeServiceRequestWithProposal();

        Livewire::actingAs($client)
            ->test(ServiceRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('rejectProposal', $proposal->id);

        $this->assertSame('rejected', $proposal->fresh()->status);
    }

    public function test_the_client_cannot_reject_a_proposal_while_a_payment_is_in_progress_for_it(): void
    {
        Notification::fake();
        [$serviceRequest, $proposal, $client, $prestataire] = $this->makeServiceRequestWithProposal();

        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'service_request_id' => $serviceRequest->id,
            'proposal_id' => $proposal->id,
            'amount' => 5000,
            'commission' => 500,
            'prestataire_amount' => 4500,
            'delivery_time' => 3,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        Livewire::actingAs($client)
            ->test(ServiceRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('rejectProposal', $proposal->id);

        $this->assertSame('pending', $proposal->fresh()->status, 'La proposition ne doit pas être refusée tant qu\'un paiement est en cours pour elle.');
    }

    // Une fois ce paiement résolu autrement (annulé, remboursé...), le rejet doit redevenir
    // possible normalement.
    public function test_the_client_can_reject_a_proposal_once_its_order_is_no_longer_pending_payment(): void
    {
        Notification::fake();
        [$serviceRequest, $proposal, $client, $prestataire] = $this->makeServiceRequestWithProposal();

        Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'service_request_id' => $serviceRequest->id,
            'proposal_id' => $proposal->id,
            'amount' => 5000,
            'commission' => 500,
            'prestataire_amount' => 4500,
            'delivery_time' => 3,
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);

        Livewire::actingAs($client)
            ->test(ServiceRequestShow::class, ['serviceRequest' => $serviceRequest])
            ->call('rejectProposal', $proposal->id);

        $this->assertSame('rejected', $proposal->fresh()->status);
    }
}
