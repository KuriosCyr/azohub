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

        $isClient = $order->client_id === Auth::id();

        return view('reviews.create', compact('order', 'isClient'));
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

        // Déterminer le type d'avis et la personne évaluée
        $isClient = $order->client_id === Auth::id();

        $validated = $isClient
            ? $request->validate([
                'quality_rating' => 'required|integer|min:1|max:5',
                'communication_rating' => 'required|integer|min:1|max:5',
                'timeliness_rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ])
            : $request->validate([
                'clarity_rating' => 'required|integer|min:1|max:5',
                'responsiveness_rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

        // La note globale n'est plus saisie à part : elle est calculée à partir des critères
        // détaillés (arrondie au plus proche), pour qu'elle en soit toujours le reflet fidèle
        // plutôt qu'une évaluation indépendante potentiellement incohérente avec eux.
        $subRatings = $isClient
            ? [$validated['quality_rating'], $validated['communication_rating'], $validated['timeliness_rating']]
            : [$validated['clarity_rating'], $validated['responsiveness_rating']];
        $overallRating = (int) round(array_sum($subRatings) / count($subRatings));

        $review = Review::create([
            'order_id' => $order->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $isClient ? $order->prestataire_id : $order->client_id,
            'service_id' => $order->service_id,
            'review_type' => $isClient ? 'client_to_prestataire' : 'prestataire_to_client',
            'rating' => $overallRating,
            'quality_rating' => $validated['quality_rating'] ?? null,
            'communication_rating' => $validated['communication_rating'] ?? null,
            'timeliness_rating' => $validated['timeliness_rating'] ?? null,
            'clarity_rating' => $validated['clarity_rating'] ?? null,
            'responsiveness_rating' => $validated['responsiveness_rating'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        // Mettre à jour les statistiques
        if ($isClient) {
            // Mise à jour du prestataire
            $order->prestataire->updateRating();
            $order->prestataire->updateLevel();

            // Mise à jour du service (une commande négociée/offre personnalisée n'en a pas)
            $order->service?->updateRating();
        } else {
            // Le prestataire note le client : sa note n'est visible que par les prestataires
            // avec qui il a échangé (voir ClientProfile), mais elle est calculée dès maintenant.
            $order->client->updateRating();
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
        // Un avis masqué par l'admin n'est visible que par l'admin lui-même.
        if (!$review->is_visible && Auth::user()->role !== 'admin') {
            abort(404);
        }

        $review->load(['reviewer', 'reviewee', 'order.service']);

        return view('reviews.show', compact('review'));
    }
}