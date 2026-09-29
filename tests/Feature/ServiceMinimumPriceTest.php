<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Suite à un audit externe : un service pouvait être créé avec un prix de 0 FCFA (min:0), que
// FedaPay refuse de toute façon au moment de la commande — l'erreur n'apparaissait donc que
// bien plus tard, côté client, sans rapport apparent avec le service lui-même.
class ServiceMinimumPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_service_cannot_be_created_with_a_price_below_100_fcfa(): void
    {
        Storage::fake('public');

        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $response = $this->actingAs($prestataire)->post(route('prestataire.services.store'), [
            'category_id' => $category->id,
            'title' => 'Service pas cher',
            'description' => str_repeat('Description suffisamment longue pour passer la validation. ', 2),
            'price' => 0,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'serves_nationwide' => true,
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertSessionHasErrors('price');
        $this->assertDatabaseCount('services', 0);
    }

    public function test_a_service_can_be_created_with_the_minimum_price(): void
    {
        Storage::fake('public');

        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $category = Category::create(['name' => 'Test', 'slug' => 'test-cat-' . uniqid(), 'is_active' => true]);

        $response = $this->actingAs($prestataire)->post(route('prestataire.services.store'), [
            'category_id' => $category->id,
            'title' => 'Service au prix plancher',
            'description' => str_repeat('Description suffisamment longue pour passer la validation. ', 2),
            'price' => 100,
            'price_type' => 'fixe',
            'delivery_time' => 3,
            'revisions_included' => 2,
            'serves_nationwide' => true,
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertSessionDoesntHaveErrors('price');
        $this->assertDatabaseCount('services', 1);
    }
}
