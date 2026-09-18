<?php

namespace App\Notifications;

use App\Models\CustomOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomOfferDeclined extends Notification
{
    use Queueable;

    public function __construct(public CustomOffer $offer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre offre personnalisée n\'a pas été retenue')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("{$this->offer->client->name} a décliné votre offre « {$this->offer->title} ».")
            ->action('Voir la conversation', route('conversations.show', $this->offer->conversation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Offre déclinée',
            'message' => $this->offer->client->name . ' a décliné votre offre « ' . $this->offer->title . ' ».',
            'icon' => 'x-circle',
            'url' => route('conversations.show', $this->offer->conversation_id),
        ];
    }
}
