<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// Notification cloche uniquement : pas besoin d'un e-mail pour ça.
class ReviewResponseAdded extends Notification
{
    use Queueable;

    public function __construct(public Review $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Réponse à votre avis',
            'message' => $this->review->reviewee->name . ' a répondu à l\'avis que vous avez laissé.',
            'icon' => 'chat',
            'url' => route('reviews.show', $this->review),
        ];
    }
}
