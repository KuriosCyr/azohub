<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewReviewReceived extends Notification
{
    use Queueable;

    public function __construct(public Review $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Vous avez reçu un nouvel avis')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("{$this->review->reviewer->name} vous a laissé un avis {$this->review->rating}/5.")
            ->when($this->review->comment, fn ($mail) => $mail->line('« ' . $this->review->comment . ' »'))
            ->action('Voir l\'avis', route('reviews.show', $this->review));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouvel avis reçu',
            'message' => "{$this->review->reviewer->name} vous a donné {$this->review->rating}/5.",
            'icon' => 'star',
            'url' => route('reviews.show', $this->review),
        ];
    }
}
