<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PrestataireProfile extends Component
{
    use WithPagination;

    public User $prestataire;
    public $activeTab = 'services'; // services, reviews, about

    public function mount($username)
    {
        $this->prestataire = User::where('role', 'prestataire')
            ->where('slug', $username)
            ->firstOrFail();

        // Vérifier que le compte est actif
        if (!$this->prestataire->is_active) {
            abort(404, 'Ce profil n\'est pas disponible.');
        }

        // Statistiques (avantage des plans payants) : on ne compte pas les visites
        // du prestataire sur son propre profil.
        if (Auth::id() !== $this->prestataire->id) {
            ProfileView::create([
                'prestataire_id' => $this->prestataire->id,
                'viewer_id' => Auth::id(),
                'viewed_at' => now(),
            ]);
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function contactPrestataire()
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Veuillez vous connecter pour contacter ce prestataire.');
        }

        if (!auth()->user()->isClient()) {
            session()->flash('error', 'Seuls les clients peuvent contacter directement un prestataire.');
            return;
        }

        $conversation = Conversation::firstOrCreate([
            'client_id' => auth()->id(),
            'prestataire_id' => $this->prestataire->id,
        ]);

        return redirect()->route('conversations.show', $conversation);
    }

    public function render()
    {
        $data = [];

        // Charger les données selon l'onglet actif
        if ($this->activeTab === 'services') {
            $data['services'] = $this->prestataire->services()
                ->active()
                ->with('category')
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->paginate(12);
        } elseif ($this->activeTab === 'reviews') {
            $data['reviews'] = $this->prestataire->receivedReviews()
                ->visible()
                ->with(['reviewer', 'order.service'])
                ->latest()
                ->paginate(10);
        }

        // Statistiques
        $data['stats'] = [
            'services_count' => $this->prestataire->services()->active()->count(),
            'total_orders' => $this->prestataire->completed_orders,
            'rating' => $this->prestataire->rating,
            'total_reviews' => $this->prestataire->receivedReviews()->visible()->count(),
            'response_time' => $this->prestataire->responseTimeLabel(),
            'completion_rate' => $this->prestataire->completionRate(),
        ];

        return view('livewire.prestataire-profile', $data)->layout('components.layouts.public');
    }
}