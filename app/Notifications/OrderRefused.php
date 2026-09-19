<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderRefused extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre commande a été refusée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre commande {$this->order->order_number} pour « {$this->order->display_title} » a été refusée par le prestataire.")
            ->line($this->order->cancellation_reason ? 'Raison : ' . $this->order->cancellation_reason : 'Vous serez remboursé intégralement.')
            ->action('Voir la commande', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Commande refusée',
            'message' => "Votre commande {$this->order->order_number} a été refusée. Vous serez remboursé.",
            'icon' => 'x-mark',
            'url' => route('orders.show', $this->order),
        ];
    }
}
