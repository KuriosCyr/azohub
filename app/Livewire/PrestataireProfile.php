<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PrestataireProfile extends Component
{
    use WithPagination;

    public User $prestataire;
    public $activeTab = 'services'; // services, reviews, about

    public function mount($username)
    {
        // Trouver le prestataire par son slug/username
        $this->prestataire = User::where('role', 'prestataire')
            ->where('id', $username) // On utilisera l'ID pour l'instant
            ->firstOrFail();

        // Vérifier que le compte est actif
        if (!$this->prestataire->is_active) {
            abort(404, 'Ce profil n\'est pas disponible.');
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
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
                ->with(['reviewer', 'order.service'])
                ->latest()
                ->paginate(10);
        }

        // Statistiques
        $data['stats'] = [
            'services_count' => $this->prestataire->services()->active()->count(),
            'total_orders' => $this->prestataire->completed_orders,
            'rating' => $this->prestataire->rating,
            'total_reviews' => $this->prestataire->receivedReviews()->count(),
            'response_time' => '< 2h', // À calculer plus tard
            'completion_rate' => $this->prestataire->completed_orders > 0 
                ? round(($this->prestataire->completed_orders / ($this->prestataire->completed_orders + 1)) * 100) 
                : 0,
        ];

        return view('livewire.prestataire-profile', $data)->layout('components.layouts.public');
    }
}