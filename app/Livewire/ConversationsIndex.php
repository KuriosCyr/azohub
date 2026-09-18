<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ConversationsIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $userId = Auth::id();
        $column = Auth::user()->isPrestataire() ? 'prestataire_id' : 'client_id';

        $conversations = Conversation::where($column, $userId)
            ->with(['client', 'prestataire', 'service'])
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('is_read', false)->where('sender_id', '!=', $userId);
            }])
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate(15);

        return view('livewire.conversations-index', [
            'conversations' => $conversations,
        ])->layout('components.layouts.app');
    }
}
