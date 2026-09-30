<?php

namespace Tests\Feature;

use App\Livewire\OrderCreate;
use App\Models\Category;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

// Corrige un point signalé par un audit externe : contrairement à ProposalAccept/
// CustomOfferAccept, OrderCreate créait une nouvelle commande à CHAQUE tentative (ex. code
// promo invalide, paiement jamais finalisé) au lieu de reprendre la précédente — laissant des
// commandes "jamais payées" orphelines en base à chaque nouvel essai.
class OrderCreateReuseTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(User $prestataire): Service
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        return Service::create([
            'user_id' => $prestataire->id,
            'category_id' => $category->id,
            'title' => 'Service test',
            'slug' => 'service-test-' . uniqid(),
            'description' => str_repeat('Description de test suffisamment longue. ', 2),
            'price' => 10000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_retrying_after_an_invalid_promo_code_reuses_the_same_order(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $service = $this->makeService($prestataire);

        $component = Livewire::withQueryParams(['service' => $service->id])
            ->actingAs($client)
            ->test(OrderCreate::class)
            ->set('requirements', str_repeat('Description suffisamment longue pour la commande. ', 2))
            ->set('paymentMethod', 'mtn_momo')
            ->set('promoCode', 'CODEINVALIDE')
            ->call('placeOrder');

        $component->assertHasErrors('promoCode');
        $this->assertSame(1, Order::where('client_id', $client->id)->count());
        $firstOrderId = Order::where('client_id', $client->id)->first()->id;

        // Deuxième tentative : sans code promo cette fois. Doit reprendre la même commande.
        $component->set('promoCode', '')->call('placeOrder');

        $this->assertSame(1, Order::where('client_id', $client->id)->count());
        $this->assertSame($firstOrderId, Order::where('client_id', $client->id)->first()->id);
    }

    public function test_a_successful_retry_with_a_valid_promo_code_still_reuses_the_same_order(): void
    {
        PromoCode::create(['code' => 'VALIDE', 'type' => 'fixed', 'value' => 500]);
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $service = $this->makeService($prestataire);

        Livewire::withQueryParams(['service' => $service->id])
            ->actingAs($client)
            ->test(OrderCreate::class)
            ->set('requirements', str_repeat('Description suffisamment longue pour la commande. ', 2))
            ->set('paymentMethod', 'mtn_momo')
            ->set('promoCode', 'CODEINVALIDE')
            ->call('placeOrder')
            ->assertHasErrors('promoCode');

        $order = Order::where('client_id', $client->id)->firstOrFail();

        // La suite de la tentative (code valide) est vérifiée directement sur le modèle plutôt
        // que via le composant, pour ne jamais déclencher le véritable appel réseau FedaPay
        // (PaymentService::initiateForOrder) dans la suite automatisée — comme pour tous les
        // autres tests de cette codebase touchant au paiement.
        $error = $order->applyPromoCode('VALIDE');

        $this->assertNull($error);
        $this->assertSame(1, Order::where('client_id', $client->id)->count());
        $this->assertEquals(500, $order->fresh()->promo_discount_applied);
    }

    // Corrigé suite à un 2e audit externe : reprendre une commande sans re-sélectionner de
    // fichier effaçait les pièces jointes de la tentative précédente (attachments écrasé à
    // null) au lieu de les conserver.
    public function test_retrying_without_new_attachments_keeps_the_previous_ones(): void
    {
        Storage::fake('local');
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $service = $this->makeService($prestataire);

        Livewire::withQueryParams(['service' => $service->id])
            ->actingAs($client)
            ->test(OrderCreate::class)
            ->set('requirements', str_repeat('Description suffisamment longue pour la commande. ', 2))
            ->set('paymentMethod', 'mtn_momo')
            ->set('attachments', [UploadedFile::fake()->create('cahier-des-charges.pdf', 100)])
            ->set('promoCode', 'CODEINVALIDE')
            ->call('placeOrder')
            ->assertHasErrors('promoCode');

        $order = Order::where('client_id', $client->id)->firstOrFail();
        $this->assertCount(1, $order->attachments);
        $firstPath = $order->attachments[0]['path'];

        // Nouvel essai, sans re-sélectionner de fichier.
        Livewire::withQueryParams(['service' => $service->id])
            ->actingAs($client)
            ->test(OrderCreate::class)
            ->set('requirements', str_repeat('Description suffisamment longue pour la commande. ', 2))
            ->set('paymentMethod', 'mtn_momo')
            ->set('promoCode', '')
            ->call('placeOrder');

        $order->refresh();
        $this->assertCount(1, $order->attachments);
        $this->assertSame($firstPath, $order->attachments[0]['path']);
        Storage::disk('local')->assertExists($firstPath);
    }

    public function test_retrying_with_new_attachments_deletes_the_orphaned_previous_files(): void
    {
        Storage::fake('local');
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $service = $this->makeService($prestataire);

        Livewire::withQueryParams(['service' => $service->id])
            ->actingAs($client)
            ->test(OrderCreate::class)
            ->set('requirements', str_repeat('Description suffisamment longue pour la commande. ', 2))
            ->set('paymentMethod', 'mtn_momo')
            ->set('attachments', [UploadedFile::fake()->create('premier-essai.pdf', 100)])
            ->set('promoCode', 'CODEINVALIDE')
            ->call('placeOrder')
            ->assertHasErrors('promoCode');

        $order = Order::where('client_id', $client->id)->firstOrFail();
        $firstPath = $order->attachments[0]['path'];
        Storage::disk('local')->assertExists($firstPath);

        // Nouvel essai, avec un fichier différent cette fois.
        Livewire::withQueryParams(['service' => $service->id])
            ->actingAs($client)
            ->test(OrderCreate::class)
            ->set('requirements', str_repeat('Description suffisamment longue pour la commande. ', 2))
            ->set('paymentMethod', 'mtn_momo')
            ->set('attachments', [UploadedFile::fake()->create('deuxieme-essai.pdf', 100)])
            ->set('promoCode', '')
            ->call('placeOrder');

        $order->refresh();
        $this->assertCount(1, $order->attachments);
        $this->assertNotSame($firstPath, $order->attachments[0]['path']);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($order->attachments[0]['path']);
    }
}
