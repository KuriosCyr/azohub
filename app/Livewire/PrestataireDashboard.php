<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Service;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PrestataireDashboard extends Component
{
    public $activeTab = 'overview';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $user = Auth::user();

        // Stats générales avec valeurs par défaut
        $stats = [
            'services_count' => $user->services()->active()->count(),
            'total_orders' => $user->prestataireOrders()->count(),
            'pending_orders' => $user->prestataireOrders()->where('status', 'paid')->count(),
            'in_progress_orders' => $user->prestataireOrders()->where('status', 'in_progress')->count(),
            'completed_orders' => $user->completed_orders ?? 0,
            'wallet_balance' => $user->wallet_balance ?? 0, // ← IMPORTANT
            'total_earnings' => $user->prestataireOrders()
                ->where('payment_status', 'released')
                ->sum('prestataire_amount') ?? 0,
            'pending_earnings' => $user->prestataireOrders()
                ->where('payment_status', 'held')
                ->sum('prestataire_amount') ?? 0,
            'rating' => $user->rating ?? 0,
            'total_reviews' => $user->total_reviews ?? 0,
        ];

        // Services récents
        $recentServices = $user->services()
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        // Commandes en cours
        $activeOrders = $user->prestataireOrders()
            ->with(['client', 'service', 'serviceRequest.category', 'customOffer'])
            ->whereIn('status', ['paid', 'in_progress'])
            ->latest()
            ->take(10)
            ->get();

        // Commandes récentes terminées
        $recentCompletedOrders = $user->prestataireOrders()
            ->with(['client', 'service', 'serviceRequest.category', 'customOffer'])
            ->where('status', 'completed')
            ->latest()
            ->take(5)
            ->get();

        // Avis récents
        $recentReviews = $user->receivedReviews()
            ->visible()
            ->with(['reviewer', 'order.service'])
            ->where('review_type', 'client_to_prestataire')
            ->latest()
            ->take(5)
            ->get();

        // Revenus des 7 derniers jours
        $weeklyEarnings = $this->getWeeklyEarnings($user);

        return view('livewire.prestataire-dashboard', [
            'stats' => $stats,
            'recentServices' => $recentServices,
            'activeOrders' => $activeOrders,
            'recentCompletedOrders' => $recentCompletedOrders,
            'recentReviews' => $recentReviews,
            'weeklyEarnings' => $weeklyEarnings,
        ]);
    }

    private function getWeeklyEarnings($user)
    {
        $earnings = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('D');
            
            $dayEarnings = $user->prestataireOrders()
                ->where('payment_status', 'released')
                ->whereDate('validated_at', $date)
                ->sum('prestataire_amount');
            
            $earnings[] = $dayEarnings ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $earnings,
        ];
    }
}