<?php

namespace App\Livewire;

use App\Models\ConversationMessage;
use App\Models\Message;
use App\Models\Service;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PrestataireDashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Stats générales avec valeurs par défaut
        $stats = [
            'services_count' => $user->services()->count(),
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
            'unread_messages' => $this->getUnreadMessagesCount($user->id),
        ];

        // Commandes en cours
        $activeOrders = $user->prestataireOrders()
            ->with(['client', 'service', 'serviceRequest.category', 'customOffer'])
            ->whereIn('status', ['paid', 'in_progress'])
            ->latest()
            ->take(10)
            ->get();

        // Avis récents (aperçu court ; la liste complète vit sur prestataire.reviews.index)
        $recentReviews = $user->receivedReviews()
            ->visible()
            ->with(['reviewer', 'order.service'])
            ->where('review_type', 'client_to_prestataire')
            ->latest()
            ->take(3)
            ->get();

        // Revenus des 30 derniers jours
        $monthlyEarnings = $this->getMonthlyEarnings($user);

        return view('livewire.prestataire-dashboard', [
            'stats' => $stats,
            'activeOrders' => $activeOrders,
            'recentReviews' => $recentReviews,
            'monthlyEarnings' => $monthlyEarnings,
            'todoItems' => $this->getTodoItems($user),
            'slotsUsed' => $user->serviceSlotsUsed(),
            'slotsMax' => $user->maxServices(),
            'currentPlan' => $user->currentPlan(),
            'activeSubscription' => $user->activeSubscription,
            'welcomePromoEndsAt' => $user->welcomePromoEndsAt(),
            'hasFreeServiceSlot' => $user->hasFreeServiceSlot(),
        ]);
    }

    // Même requête que le badge "Messages" de la barre de navigation (chat direct + chat de commande).
    private function getUnreadMessagesCount(int $userId): int
    {
        return ConversationMessage::whereHas('conversation', function ($q) use ($userId) {
                $q->where('client_id', $userId)->orWhere('prestataire_id', $userId);
            })
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count()
            + Message::where('receiver_id', $userId)->where('is_read', false)->count();
    }

    // « À faire maintenant » : ce qui demande une action du prestataire, le plus urgent en premier.
    private function getTodoItems($user): array
    {
        $items = [];

        if (!$user->identity_verified && $user->identity_verification_status !== 'pending') {
            $items[] = [
                'type' => 'identity',
                'title' => 'Vérifiez votre identité',
                'subtitle' => 'Requis pour pouvoir retirer vos gains — ça prend deux minutes',
            ];
        }

        if ($user->isTrialEligible()) {
            $items[] = [
                'type' => 'trial',
                'title' => 'Profitez de votre mois offert !',
                'subtitle' => 'Commission réduite et plus de services publiables, sans rien payer — un plan au choix, un seul clic',
            ];
        }

        $dueOrders = $user->prestataireOrders()
            ->whereIn('status', ['paid', 'in_progress'])
            ->whereNotNull('expected_delivery_at')
            ->where('expected_delivery_at', '<=', now()->addDays(2))
            ->orderBy('expected_delivery_at')
            ->get();

        if ($dueOrders->isNotEmpty()) {
            $overdue = $dueOrders->where('expected_delivery_at', '<', now())->count();
            $items[] = [
                'type' => 'delivery',
                'title' => $dueOrders->count() > 1
                    ? $dueOrders->count() . ' commandes à livrer' . ($overdue > 0 ? ' — dont ' . $overdue . ' en retard' : ' bientôt')
                    : '1 commande à livrer' . ($overdue > 0 ? ', en retard' : ' avant le ' . $dueOrders->first()->expected_delivery_at->translatedFormat('d M à H\hi')),
                'subtitle' => $dueOrders->pluck('display_title')->take(3)->implode(', '),
            ];
        }

        $revisionOrders = $user->prestataireOrders()->where('revision_requested', true)->get();
        if ($revisionOrders->isNotEmpty()) {
            $items[] = [
                'type' => 'revision',
                'title' => $revisionOrders->count() > 1
                    ? $revisionOrders->count() . ' révisions demandées'
                    : '1 révision demandée',
                'subtitle' => 'Le client souhaite une modification sur « ' . $revisionOrders->first()->display_title . ' »',
            ];
        }

        $unread = $this->getUnreadMessagesCount($user->id);
        if ($unread > 0) {
            $items[] = [
                'type' => 'messages',
                'title' => $unread > 1 ? $unread . ' nouveaux messages' : '1 nouveau message',
                'subtitle' => 'Répondez vite pour ne pas perdre la commande',
            ];
        }

        $rejectedServices = Service::where('user_id', $user->id)->where('status', 'rejected')->get();
        if ($rejectedServices->isNotEmpty()) {
            $first = $rejectedServices->first();
            $items[] = [
                'type' => 'rejected',
                'title' => $rejectedServices->count() > 1
                    ? $rejectedServices->count() . ' services refusés'
                    : 'Service « ' . $first->title . ' » refusé',
                'subtitle' => 'Motif : ' . ($first->moderation_note ?: 'non précisé') . ' — modifiez-le pour le soumettre à nouveau',
            ];
        }

        return $items;
    }

    private function getMonthlyEarnings($user)
    {
        $earnings = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');

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
