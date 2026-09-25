<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// Envoyée au PRESTATAIRE avant l'échéance de livraison d'une commande (12h puis 1h avant),
// tant qu'elle n'a pas encore été livrée. Voir RemindOrderDeadlines.
class OrderDeadlineApproaching extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public int $hoursRemaining)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $delay = $this->hoursRemaining <= 1 ? 'dans moins d\'1 heure' : "dans environ {$this->hoursRemaining} heures";

        return (new MailMessage)
            ->subject('Livraison à rendre ' . $delay . ' — ' . $this->order->order_number)
            ->greeting('Attention, ' . $notifiable->name . ' !')
            ->line("La livraison de la commande {$this->order->order_number} (« {$this->order->display_title} ») est attendue {$delay}, le {$this->order->expected_delivery_at->translatedFormat('d M Y à H\hi')}.")
            ->line('Livrez à temps pour préserver votre taux de ponctualité et la satisfaction du client.')
            ->action('Livrer maintenant', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        $delay = $this->hoursRemaining <= 1 ? 'moins d\'1 heure' : "{$this->hoursRemaining} heures";

        return [
            'title' => 'Livraison bientôt due',
            'message' => "Commande {$this->order->order_number} à livrer dans {$delay}.",
            'icon' => 'exclamation-triangle',
            'url' => route('orders.show', $this->order),
        ];
    }
}
