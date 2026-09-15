<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Notifications\NewReviewReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Show review form.
     */
    public function create(Order $order)
    {
        // Vérifier que la commande est terminée
        if ($order->status !== 'completed') {
            return redirect()
                ->back()
                ->with('error', 'Vous ne pouvez laisser un avis que sur une commande terminée.');
        }

        // Vérifier que l'utilisateur fait partie de la commande
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier si un avis existe déjà
        $existingReview = Review::where('order_id', $order->id)
            ->where('reviewer_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()
                ->back()
                ->with('error', 'Vous avez déjà laissé un avis pour cette commande.');
        }

        $order->load(['client', 'prestataire', 'service']);

        return view('reviews.create', compact('order'));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, Order $order)
    {
        // Vérifier que la commande est terminée
        if ($order->status !== 'completed') {
            return redirect()
                ->back()
                ->with('error', 'Vous ne pouvez laisser un avis que sur une commande terminée.');
        }

        // Vérifier que l'utilisateur fait partie de la commande
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier si un avis existe déjà
        $existingReview = Review::where('order_id', $order->id)
            ->where('reviewer_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()
                ->back()
                ->with('error', 'Vous avez déjà laissé un avis pour cette commande.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'quality_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'timeliness_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Déterminer le type d'avis et la personne évaluée
        $isClient = $order->client_id === Auth::id();
        
        $review = Review::create([
            'order_id' => $order->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $isClient ? $order->prestataire_id : $order->client_id,
            'service_id' => $order->service_id,
            'review_type' => $isClient ? 'client_to_prestataire' : 'prestataire_to_client',
            'rating' => $validated['rating'],
            'quality_rating' => $validated['quality_rating'],
            'communication_rating' => $validated['communication_rating'],
            'timeliness_rating' => $validated['timeliness_rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        // Mettre à jour les statistiques
        if ($isClient) {
            // Mise à jour du prestataire
            $order->prestataire->updateRating();
            
            // Mise à jour du service
            $order->service->updateRating();
        }

        $review->reviewee->notify(new NewReviewReceived($review));

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Merci pour votre avis !');
    }

    /**
     * Show review.
     */
    public function show(Review $review)
    {
        $review->load(['reviewer', 'reviewee', 'order.service']);

        return view('reviews.show', compact('review'));
    }
}