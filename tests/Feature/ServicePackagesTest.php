<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Livewire\ServiceShow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

// Suggestion d'un audit externe, implémentée : OrderCreate lisait déjà un paramètre ?package=
// mais l'ignorait totalement — aucune formule Basique/Standard/Premium n'existait nulle part.
class ServicePackagesTest extends TestCase
{
    use RefreshDatabase;

    private function makePackagePayload(): array
    {
        return [
            'basic' => ['price' => 5000, 'delivery_time' => 2, 'revisions_included' => 1, 'description' => 'Version simple'],
            'standard' => ['price' => 10000, 'delivery_time' => 4, 'revisions_included' => 2, 'description' => 'Version standard'],
            'premium' => ['price' => 20000, 'delivery_time' => 7, 'revisions_included' => 5, 'description' => 'Version complète'],
        ];
    }

    public function test_a_prestataire_can_create_a_service_with_three_packages(): void
    {
        Storage::fake('public');

        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $response = $this->actingAs($prestataire)->post(route('prestataire.services.store'), [
            'category_id' => $category->id,
            'title' => 'Service avec formules',
            'description' => str_repeat('Description suffisamment longue pour passer la validation. ', 2),
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'serves_nationwide' => true,
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'has_packages' => '1',
            'packages' => $this->makePackagePayload(),
        ]);

        $response->assertSessionDoesntHaveErrors();
        $service = Service::where('title', 'Service avec formules')->firstOrFail();

        $this->assertTrue($service->hasPackages());
        $this->assertSame(3, $service->packages->count());
        $this->assertEquals(20000, $service->packages->firstWhere('tier', 'premium')->price);
    }

    public function test_unchecking_packages_on_update_removes_them(): void
    {
        Storage::fake('public');

        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $service = Service::create([
            'user_id' => $prestataire->id,
            'category_id' => $category->id,
            'title' => 'Service test',
            'slug' => 'service-test-' . uniqid(),
            'description' => str_repeat('Description de test suffisamment longue. ', 2),
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'status' => 'active',
            'is_active' => true,
        ]);

        ServicePackage::create(['service_id' => $service->id, 'tier' => 'basic', 'price' => 5000, 'delivery_time' => 2, 'revisions_included' => 1]);
        ServicePackage::create(['service_id' => $service->id, 'tier' => 'standard', 'price' => 10000, 'delivery_time' => 4, 'revisions_included' => 2]);
        ServicePackage::create(['service_id' => $service->id, 'tier' => 'premium', 'price' => 20000, 'delivery_time' => 7, 'revisions_included' => 5]);

        $this->actingAs($prestataire)->put(route('prestataire.services.update', $service), [
            'category_id' => $category->id,
            'title' => $service->title,
            'description' => $service->description,
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'serves_nationwide' => true,
            'is_active' => true,
            'has_packages' => '0',
        ]);

        $this->assertSame(0, $service->packages()->count());
        // Corrigé suite à un audit externe : désactiver les formules contournait la
        // modération — le service publié restait "active" alors que son contenu venait de
        // changer.
        $this->assertSame('pending', $service->fresh()->status);
    }

    // Corrigé suite à un audit externe : ServiceController ne comparait que les champs du
    // service lui-même (titre, prix, description...) pour décider de repasser en modération —
    // les formules (prix, délai, description libre non modérée) pouvaient être modifiées
    // librement sur un service déjà publié, sans jamais repasser par un contrôle admin.
    public function test_changing_a_package_price_sends_the_service_back_to_moderation(): void
    {
        Storage::fake('public');

        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $service = Service::create([
            'user_id' => $prestataire->id,
            'category_id' => $category->id,
            'title' => 'Service test',
            'slug' => 'service-test-' . uniqid(),
            'description' => str_repeat('Description de test suffisamment longue. ', 2),
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'status' => 'active',
            'is_active' => true,
        ]);

        ServicePackage::create(['service_id' => $service->id, 'tier' => 'basic', 'price' => 5000, 'delivery_time' => 2, 'revisions_included' => 1]);
        ServicePackage::create(['service_id' => $service->id, 'tier' => 'standard', 'price' => 10000, 'delivery_time' => 4, 'revisions_included' => 2]);
        ServicePackage::create(['service_id' => $service->id, 'tier' => 'premium', 'price' => 20000, 'delivery_time' => 7, 'revisions_included' => 5]);

        $payload = $this->makePackagePayload();
        $payload['premium']['price'] = 99000; // seule différence avec les valeurs existantes

        $this->actingAs($prestataire)->put(route('prestataire.services.update', $service), [
            'category_id' => $category->id,
            'title' => $service->title,
            'description' => $service->description,
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'serves_nationwide' => true,
            'is_active' => true,
            'has_packages' => '1',
            'packages' => $payload,
        ]);

        $this->assertSame('pending', $service->fresh()->status);
        $this->assertEquals(99000, $service->packages()->where('tier', 'premium')->first()->price);
    }

    // Teste directement ServiceController::packagesChanged() (privée) par réflexion : isoler
    // cette méthode évite les faux positifs d'un test HTTP bout en bout, où d'autres champs
    // (ex. "tags" : parseTags() ne renvoie jamais null, contrairement à la valeur par défaut en
    // base) déclenchent leur propre isDirty() sans rapport avec les formules.
    public function test_packages_changed_detects_no_change_when_data_is_identical(): void
    {
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $service = Service::create([
            'user_id' => $prestataire->id,
            'category_id' => $category->id,
            'title' => 'Service test',
            'slug' => 'service-test-' . uniqid(),
            'description' => str_repeat('Description de test suffisamment longue. ', 2),
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'status' => 'active',
            'is_active' => true,
        ]);

        $payload = $this->makePackagePayload();
        foreach ($payload as $tier => $data) {
            ServicePackage::create(array_merge(['service_id' => $service->id, 'tier' => $tier], $data));
        }

        $request = \Illuminate\Http\Request::create('/', 'PUT', ['has_packages' => '1']);
        $controller = new \App\Http\Controllers\ServiceController();
        $method = new \ReflectionMethod($controller, 'packagesChanged');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($controller, $service, $request, ['packages' => $payload]));

        $changedPayload = $payload;
        $changedPayload['premium']['price'] = 99000;
        $this->assertTrue($method->invoke($controller, $service, $request, ['packages' => $changedPayload]));
    }

    public function test_selecting_a_package_on_the_service_page_carries_through_to_the_order(): void
    {
        Storage::fake('public');

        // Rôle explicite : /orders/create est protégée par le middleware 'client', qui vérifie
        // auth()->user()->role — sans ça, le modèle en mémoire renvoyé par create() n'a pas
        // encore le défaut 'client' appliqué par la base (il faudrait le recharger).
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $service = Service::create([
            'user_id' => $prestataire->id,
            'category_id' => $category->id,
            'title' => 'Service à formules',
            'slug' => 'service-formules-' . uniqid(),
            'description' => str_repeat('Description de test suffisamment longue. ', 2),
            'price' => 5000,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'status' => 'active',
            'is_active' => true,
        ]);

        ServicePackage::create(['service_id' => $service->id, 'tier' => 'basic', 'price' => 5000, 'delivery_time' => 2, 'revisions_included' => 1]);
        ServicePackage::create(['service_id' => $service->id, 'tier' => 'premium', 'price' => 30000, 'delivery_time' => 10, 'revisions_included' => 5]);

        // Le client choisit "Premium" sur la page du service : selectPackage() met bien à jour
        // la propriété qu'orderService() utilise pour construire l'URL vers /orders/create.
        Livewire::actingAs($client)
            ->test(ServiceShow::class, ['service' => $service])
            ->call('selectPackage', 'premium')
            ->assertSet('selectedPackage', 'premium');

        // ... arrive sur /orders/create?service=X&package=premium (URL construite par
        // orderService()) : la page doit refléter le prix Premium, pas le prix de base du service.
        $response = $this->actingAs($client)
            ->get(route('orders.create', ['service' => $service->id, 'package' => 'premium']));

        $response->assertOk();
        $response->assertSee('30 000');
        $response->assertDontSee('5 000 FCFA', false);
    }
}
