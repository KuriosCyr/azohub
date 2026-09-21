<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Service;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ClientDashboard extends Component
{
    // Filtres pour les commandes
    public $statusFilter = 'all';
    public $searchQuery = '';

    /**
     * Statistiques globales
     */
    public function getStatsProperty()
    {
        $userId = Auth::id();

        return [
            'total_orders' => Order::where('client_id', $userId)->count(),
            'pending_orders' => Order::where('client_id', $userId)
                ->whereIn('status', ['pending_payment', 'paid', 'in_progress'])
                ->count(),
            'completed_orders' => Order::where('client_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'cancelled_orders' => Order::where('client_id', $userId)
                ->where('status', 'cancelled')
                ->count(),
            // Montant réellement débité (prix + frais de service client), commandes payées.
            'total_spent' => (float) Order::where('client_id', $userId)
                ->whereIn('status', ['paid', 'in_progress', 'delivered', 'completed'])
                ->sum(\Illuminate\Support\Facades\DB::raw('amount + COALESCE(client_fee, 0)')),
            'awaiting_validation' => Order::where('client_id', $userId)
                ->where('status', 'delivered')
                ->count(),
        ];
    }

    /**
     * Commandes récentes
     */
    public function getRecentOrdersProperty()
    {
        return Order::where('client_id', Auth::id())
            ->with(['service.prestataire', 'service.category', 'serviceRequest.category', 'customOffer'])
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Commandes en attente d'action du client
     */
    public function getActionRequiredOrdersProperty()
    {
        return Order::where('client_id', Auth::id())
            ->where('status', 'delivered')
            ->with(['service.prestataire', 'service.category', 'serviceRequest.category', 'customOffer'])
            ->latest()
            ->get();
    }

    /**
     * Commandes filtrées
     */
    public function getOrdersProperty()
    {
        $query = Order::where('client_id', Auth::id())
            ->with(['service.prestataire', 'service.category', 'serviceRequest.category', 'customOffer']);

        // Filtre par statut
        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'active') {
                $query->whereIn('status', ['pending_payment', 'paid', 'in_progress']);
            } elseif ($this->statusFilter === 'awaiting') {
                $query->where('status', 'delivered');
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        // Recherche
        if (!empty($this->searchQuery)) {
            // Les commandes issues d'une demande/proposition ou d'une offre personnalisée n'ont
            // pas de service : on cherche aussi dans le titre de ces origines.
            $term = '%' . $this->searchQuery . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('service', fn ($s) => $s->where('title', 'like', $term))
                  ->orWhereHas('serviceRequest', fn ($s) => $s->where('title', 'like', $term))
                  ->orWhereHas('customOffer', fn ($s) => $s->where('title', 'like', $term));
            });
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Services recommandés (populaires)
     */
    public function getRecommendedServicesProperty()
    {
        return Service::active()
            ->with(['prestataire', 'category'])
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.client-dashboard', [
            'stats' => $this->stats,
            'recentOrders' => $this->recentOrders,
            'actionRequiredOrders' => $this->actionRequiredOrders,
            'orders' => $this->orders,
            'recommendedServices' => $this->recommendedServices,
        ])->layout('components.layouts.app');
    }
}