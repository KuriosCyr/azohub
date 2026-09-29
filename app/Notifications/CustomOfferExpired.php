<?php

namespace App\Notifications;

use App\Models\CustomOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// Envoyée au prestataire quand son offre personnalisée expire faute de réponse du client (voir
// ExpireStaleOffers) — distincte de CustomOfferDeclined : le client n'a pas activement refusé.
class CustomOfferExpired extends Notification implements ShouldQueue
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
            ->subject('Votre offre personnalisée a expiré')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre offre « {$this->offer->title} » envoyée à {$this->offer->client->name} a expiré sans réponse.")
            ->line('Vous pouvez en envoyer une nouvelle depuis la conversation si l\'échange est toujours d\'actualité.')
            ->action('Voir la conversation', route('conversations.show', $this->offer->conversation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Offre expirée',
            'message' => 'Votre offre « ' . $this->offer->title . ' » a expiré sans réponse de ' . $this->offer->client->name . '.',
            'icon' => 'clock',
            'url' => route('conversations.show', $this->offer->conversation_id),
        ];
    }
}
