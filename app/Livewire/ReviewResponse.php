<?php

namespace App\Livewire;

use App\Models\Review;
use App\Notifications\ReviewResponseAdded;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

// Réponse (facultative, unique, définitive) du prestataire à un avis client -> prestataire.
// Embarqué partout où un avis s'affiche (fiche commande, profil public, tableau de bord) :
// lecture seule si une réponse existe déjà, formulaire seulement pour le prestataire concerné.
class ReviewResponse extends Component
{
    public Review $review;
    public bool $showForm = false;
    public string $text = '';

    public function mount(Review $review)
    {
        $this->review = $review;
    }

    public function getCanRespondProperty(): bool
    {
        return Auth::check()
            && $this->review->is_visible
            && $this->review->review_type === 'client_to_prestataire'
            && Auth::id() === $this->review->reviewee_id
            && !$this->review->response;
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
    }

    public function submit()
    {
        abort_unless($this->canRespond, 403);

        $this->validate([
            'text' => 'required|string|max:1000',
        ], [], ['text' => 'réponse']);

        $this->review->respond($this->text);
        $this->review->reviewer->notify(new ReviewResponseAdded($this->review));

        $this->text = '';
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.review-response');
    }
}
