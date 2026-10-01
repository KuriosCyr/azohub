<?php

namespace Tests\Feature;

use App\Livewire\ConversationShow;
use App\Livewire\OrderChat;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

// Corrigé suite à un 6e audit externe : #[Locked] (5e audit) empêche de fixer $messagesLimit
// directement depuis le navigateur, mais loadMoreMessages() (+50 à chaque appel) restait
// appelable en boucle sans aucun plafond.
class ChatMessagesLimitCapTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_chat_messages_limit_stops_growing_at_the_cap(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'paid',
            'payment_status' => 'held',
        ]);

        $component = Livewire::actingAs($client)->test(OrderChat::class, ['order' => $order]);

        // 50 appels de +50 dépasseraient 2500 sans plafond ; doit s'arrêter à 1000.
        for ($i = 0; $i < 50; $i++) {
            $component->call('loadMoreMessages');
        }

        $this->assertEquals(1000, $component->get('messagesLimit'));
    }

    public function test_conversation_show_messages_limit_stops_growing_at_the_cap(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $conversation = Conversation::create([
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
        ]);

        $component = Livewire::actingAs($client)->test(ConversationShow::class, ['conversation' => $conversation]);

        for ($i = 0; $i < 50; $i++) {
            $component->call('loadMoreMessages');
        }

        $this->assertEquals(1000, $component->get('messagesLimit'));
    }
}
