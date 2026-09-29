<?php

namespace Tests\Feature;

use App\Livewire\ConversationShow;
use App\Livewire\ServiceRequestCreate;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Suite à un audit externe : aucune limite de fréquence sur les actions Livewire (messages,
// offres, propositions, demandes) — un compte pouvait en envoyer un nombre illimité en peu de
// temps, donc autant d'e-mails (chaque notification étant désormais en file, cf. commit sur
// ShouldQueue, mais ça ne protège pas contre le volume lui-même).
class RateLimitedActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_messages_is_blocked_after_the_limit_is_reached(): void
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $conversation = Conversation::create([
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
        ]);

        $component = Livewire::actingAs($client)->test(ConversationShow::class, ['conversation' => $conversation]);

        // La limite de ConversationShow::sendMessage() est de 20/minute.
        for ($i = 0; $i < 20; $i++) {
            $component->set('message', "Message numéro {$i}")->call('sendMessage');
        }

        $this->assertSame(20, $conversation->messages()->count());

        // Le 21e est refusé, pas enregistré.
        $component->set('message', 'Message de trop')->call('sendMessage');
        $component->assertHasErrors('message');
        $this->assertSame(20, $conversation->messages()->count());
    }

    public function test_creating_service_requests_is_blocked_after_the_limit_is_reached(): void
    {
        $client = User::factory()->create();

        $component = Livewire::actingAs($client)->test(ServiceRequestCreate::class);

        // La limite de ServiceRequestCreate::submit() est de 5/heure.
        for ($i = 0; $i < 5; $i++) {
            $component->set('categoryId', \App\Models\Category::create(['name' => "Cat {$i}", 'slug' => "cat-{$i}-" . uniqid(), 'is_active' => true])->id)
                ->set('title', "Demande numéro {$i}")
                ->set('description', str_repeat('Description suffisamment longue. ', 2))
                ->set('city', 'Cotonou')
                ->call('submit');
        }

        $this->assertSame(5, \App\Models\ServiceRequest::where('client_id', $client->id)->count());

        $component->set('title', 'Demande de trop')->call('submit');
        $component->assertHasErrors('title');
        $this->assertSame(5, \App\Models\ServiceRequest::where('client_id', $client->id)->count());
    }
}
