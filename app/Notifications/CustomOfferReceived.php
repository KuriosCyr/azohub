<?php

namespace App\Notifications;

use App\Models\CustomOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomOfferReceived extends Notification
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
            ->subject('Offre personnalisée reçue')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("{$this->offer->prestataire->name} vous a envoyé une offre personnalisée : « {$this->offer->title} » pour " . number_format((float) $this->offer->price, 0, ',', ' ') . ' FCFA.')
            ->action('Voir l\'offre', route('conversations.show', $this->offer->conversation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Offre personnalisée reçue',
            'message' => $this->offer->prestataire->name . ' vous propose « ' . $this->offer->title . ' » pour ' . number_format((float) $this->offer->price, 0, ',', ' ') . ' FCFA.',
            'icon' => 'clipboard',
            'url' => route('conversations.show', $this->offer->conversation_id),
        ];
    }
}
