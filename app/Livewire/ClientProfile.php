<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

// Mini profil d'un client, réservé aux prestataires avec qui il a déjà collaboré (commande) ou
// échangé (conversation) — jamais public, contrairement au profil prestataire.
class ClientProfile extends Component
{
    use WithPagination;

    public User $client;

    public function mount(User $client)
    {
        abort_unless($client->role === 'client', 404);

        $prestataireId = Auth::id();
        $hasRelation = Order::where('client_id', $client->id)->where('prestataire_id', $prestataireId)->exists()
            || Conversation::where('client_id', $client->id)->where('prestataire_id', $prestataireId)->exists();

        abort_unless($hasRelation, 403, "Vous ne pouvez consulter que le profil des clients avec qui vous avez échangé ou collaboré.");

        $this->client = $client;
    }

    public function render()
    {
        $reviews = $this->client->receivedReviews()
            ->where('review_type', 'prestataire_to_client')
            ->visible()
            ->with(['reviewer', 'order.service'])
            ->latest()
            ->paginate(10);

        $completedOrdersWithMe = Order::where('client_id', $this->client->id)
            ->where('prestataire_id', Auth::id())
            ->where('status', 'completed')
            ->count();

        return view('livewire.client-profile', [
            'reviews' => $reviews,
            'completedOrdersWithMe' => $completedOrdersWithMe,
        ])->layout('components.layouts.app');
    }
}
