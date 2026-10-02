<?php

namespace Tests\Feature;

use App\Livewire\ConversationShow;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Demande explicite de l'utilisateur, suite au 8e audit externe : contrairement aux commandes,
// propositions et offres (déjà bloquées pour un prestataire désactivé/supprimé), la messagerie
// n'avait aucune protection équivalente — un client pouvait continuer à écrire à un prestataire
// désactivé sans le savoir, qui était notifié normalement.
class ConversationMessagingBlockedForDeactivatedAccountTest extends TestCase
{
    use RefreshDatabase;

    private function makeConversation(): array
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $conversation = Conversation::create([
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
        ]);

        return [$client, $prestataire, $conversation];
    }

    public function test_a_message_can_be_sent_when_the_other_participant_is_active(): void
    {
        [$client, , $conversation] = $this->makeConversation();

        Livewire::actingAs($client)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('message', 'Bonjour, êtes-vous disponible ?')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertSame(1, ConversationMessage::where('conversation_id', $conversation->id)->count());
    }

    public function test_sending_a_message_is_blocked_when_the_prestataire_is_deactivated(): void
    {
        [$client, $prestataire, $conversation] = $this->makeConversation();
        $prestataire->update(['is_active' => false]);

        Livewire::actingAs($client)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('message', 'Bonjour, êtes-vous disponible ?')
            ->call('sendMessage')
            ->assertHasErrors('message');

        $this->assertSame(0, ConversationMessage::where('conversation_id', $conversation->id)->count());
    }

    public function test_sending_a_message_is_blocked_without_crashing_when_the_prestataire_was_deleted(): void
    {
        [$client, $prestataire, $conversation] = $this->makeConversation();
        $prestataire->delete();

        Livewire::actingAs($client)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('message', 'Bonjour, êtes-vous disponible ?')
            ->call('sendMessage')
            ->assertHasErrors('message');

        $this->assertSame(0, ConversationMessage::where('conversation_id', $conversation->id)->count());
    }

    // Symétrique : un prestataire ne doit pas non plus pouvoir écrire à un client désactivé
    // (répond à la question de l'utilisateur sur le blocage d'un client par l'admin).
    public function test_sending_a_message_is_blocked_when_the_client_is_deactivated(): void
    {
        [$client, $prestataire, $conversation] = $this->makeConversation();
        $client->update(['is_active' => false]);

        Livewire::actingAs($prestataire)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('message', 'Bonjour, je peux vous aider.')
            ->call('sendMessage')
            ->assertHasErrors('message');

        $this->assertSame(0, ConversationMessage::where('conversation_id', $conversation->id)->count());
    }

    public function test_sending_a_custom_offer_is_blocked_when_the_client_is_deactivated(): void
    {
        [$client, $prestataire, $conversation] = $this->makeConversation();
        $client->update(['is_active' => false]);

        Livewire::actingAs($prestataire)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('offerTitle', 'Offre sur mesure')
            ->set('offerDescription', 'Détail de l\'offre proposée.')
            ->set('offerPrice', 8000)
            ->set('offerDeliveryDays', 5)
            ->set('offerRevisions', 1)
            ->call('sendOffer')
            ->assertHasErrors('offerTitle');

        $this->assertSame(0, \App\Models\CustomOffer::where('conversation_id', $conversation->id)->count());
    }

    // Conversation::client()/prestataire() n'avaient pas withTrashed() — la page plantait
    // (otherParticipant() renvoyait null) dès qu'on l'ouvrait après la suppression d'un
    // participant, avant même d'essayer d'envoyer un message.
    public function test_viewing_the_conversation_does_not_crash_when_the_prestataire_was_deleted(): void
    {
        [$client, $prestataire, $conversation] = $this->makeConversation();
        $prestataire->delete();

        Livewire::actingAs($client)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->assertOk();
    }

    public function test_the_composer_is_hidden_and_a_banner_shown_when_the_other_participant_is_deactivated(): void
    {
        [$client, $prestataire, $conversation] = $this->makeConversation();
        $prestataire->update(['is_active' => false]);

        Livewire::actingAs($client)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->assertSee('désactivé')
            ->assertDontSee('Écrivez votre message...');
    }
}
