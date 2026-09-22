<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PrestataireReviewsIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $user = Auth::user();

        $reviews = $user->receivedReviews()
            ->where('review_type', 'client_to_prestataire')
            ->visible()
            ->with(['reviewer', 'order.service'])
            ->latest()
            ->paginate(10);

        return view('livewire.prestataire-reviews-index', [
            'reviews' => $reviews,
            'rating' => $user->rating,
            'totalReviews' => $user->total_reviews,
        ])->layout('components.layouts.app');
    }
}
