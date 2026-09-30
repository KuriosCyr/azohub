<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Corrigé suite à un 4e audit externe : n'importe quel prestataire pouvait télécharger les pièces
// jointes de n'importe quelle demande de service, même après qu'une proposition ait été acceptée
// (demande 'closed') — le prestataire retenu et ses concurrents évincés avaient exactement le
// même accès, alors que la demande ne les concerne plus une fois close.
class ServiceRequestAttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeServiceRequestWithAttachment(array $overrides = []): ServiceRequest
    {
        Storage::fake('local');
        $client = User::factory()->create(['role' => 'client']);

        $file = UploadedFile::fake()->create('cahier-des-charges.pdf', 10);
        $path = $file->store('service-requests', 'local');

        $category = Category::create(['name' => 'Design', 'slug' => 'design-' . uniqid(), 'is_active' => true]);

        return ServiceRequest::create(array_merge([
            'client_id' => $client->id,
            'category_id' => $category->id,
            'title' => 'Besoin d\'un logo',
            'description' => str_repeat('Détail de la demande. ', 5),
            'city' => 'Cotonou',
            'status' => 'open',
            'attachments' => [['path' => $path, 'name' => 'cahier-des-charges.pdf']],
        ], $overrides));
    }

    public function test_the_client_author_can_download_the_attachment(): void
    {
        $serviceRequest = $this->makeServiceRequestWithAttachment();

        $response = $this->actingAs($serviceRequest->client)
            ->get(route('service-requests.attachment.download', [$serviceRequest, 0]));

        $response->assertOk();
    }

    public function test_any_prestataire_can_download_the_attachment_while_the_request_is_open(): void
    {
        $serviceRequest = $this->makeServiceRequestWithAttachment();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $response = $this->actingAs($prestataire)
            ->get(route('service-requests.attachment.download', [$serviceRequest, 0]));

        $response->assertOk();
    }

    // Le cœur du correctif : une fois une proposition acceptée, un prestataire qui n'est pas celui
    // retenu ne doit plus pouvoir télécharger les pièces jointes de cette demande.
    public function test_a_non_retained_prestataire_cannot_download_the_attachment_once_the_request_is_closed(): void
    {
        $serviceRequest = $this->makeServiceRequestWithAttachment(['status' => 'closed']);
        $retainedPrestataire = User::factory()->create(['role' => 'prestataire']);
        $otherPrestataire = User::factory()->create(['role' => 'prestataire']);

        Proposal::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $retainedPrestataire->id,
            'message' => 'Je peux le faire.',
            'proposed_price' => 5000,
            'delivery_time' => 3,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($otherPrestataire)
            ->get(route('service-requests.attachment.download', [$serviceRequest, 0]));

        $response->assertForbidden();
    }

    public function test_the_retained_prestataire_can_still_download_the_attachment_once_the_request_is_closed(): void
    {
        $serviceRequest = $this->makeServiceRequestWithAttachment(['status' => 'closed']);
        $retainedPrestataire = User::factory()->create(['role' => 'prestataire']);

        Proposal::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $retainedPrestataire->id,
            'message' => 'Je peux le faire.',
            'proposed_price' => 5000,
            'delivery_time' => 3,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($retainedPrestataire)
            ->get(route('service-requests.attachment.download', [$serviceRequest, 0]));

        $response->assertOk();
    }

    public function test_a_prestataire_cannot_download_the_attachment_of_an_expired_request_with_no_retained_offer(): void
    {
        $serviceRequest = $this->makeServiceRequestWithAttachment(['status' => 'expired']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $response = $this->actingAs($prestataire)
            ->get(route('service-requests.attachment.download', [$serviceRequest, 0]));

        $response->assertForbidden();
    }

    public function test_the_admin_can_always_download_the_attachment(): void
    {
        $serviceRequest = $this->makeServiceRequestWithAttachment(['status' => 'closed']);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('service-requests.attachment.download', [$serviceRequest, 0]));

        $response->assertOk();
    }
}
