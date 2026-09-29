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

// Suite à un audit externe : le chat rechargeait tout l'historique à chaque sondage
// (wire:poll.5s), même pour une conversation de plusieurs centaines de messages.
class ChatPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_only_loads_the_most_recent_messages_and_can_load_more(): void
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);
        $conversation = Conversation::create([
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
        ]);

        // 60 messages, au-delà de la limite par défaut de 50.
        for ($i = 0; $i < 60; $i++) {
            $conversation->messages()->create([
                'sender_id' => $client->id,
                'message' => "Message {$i}",
            ]);
        }

        $component = Livewire::actingAs($client)->test(ConversationShow::class, ['conversation' => $conversation]);

        $component->assertViewHas('messages', fn ($messages) => $messages->count() === 50);
        $component->assertViewHas('hasMoreMessages', true);
        $component->assertSee('Charger les messages précédents');

        $component->call('loadMoreMessages');

        $component->assertViewHas('messages', fn ($messages) => $messages->count() === 60);
        $component->assertViewHas('hasMoreMessages', false);
    }

    public function test_order_chat_only_loads_the_most_recent_messages_and_can_load_more(): void
    {
        $client = User::factory()->create();
        $prestataire = User::factory()->create(['role' => 'prestataire']);

        $order = Order::create([
            'order_number' => 'AZH-TEST-' . uniqid(),
            'client_id' => $client->id,
            'prestataire_id' => $prestataire->id,
            'amount' => 10000,
            'commission' => 1000,
            'prestataire_amount' => 9000,
            'delivery_time' => 3,
            'status' => 'in_progress',
            'payment_status' => 'held',
        ]);

        for ($i = 0; $i < 55; $i++) {
            $order->messages()->create([
                'sender_id' => $client->id,
                'receiver_id' => $prestataire->id,
                'message' => "Message {$i}",
            ]);
        }

        $component = Livewire::actingAs($client)->test(OrderChat::class, ['order' => $order]);

        $component->assertViewHas('messages', fn ($messages) => $messages->count() === 50);
        $component->assertViewHas('hasMoreMessages', true);

        $component->call('loadMoreMessages');

        $component->assertViewHas('messages', fn ($messages) => $messages->count() === 55);
        $component->assertViewHas('hasMoreMessages', false);
    }
}
