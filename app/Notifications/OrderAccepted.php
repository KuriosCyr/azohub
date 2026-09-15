<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderAccepted extends Notification
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
            ->subject('Votre commande a été acceptée')
            ->greeting('Bonne nouvelle, ' . $notifiable->name . ' !')
            ->line("Votre commande {$this->order->order_number} pour « {$this->order->service->title} » a été acceptée par le prestataire.")
            ->action('Voir la commande', route('orders.show', $this->order))
            ->line('Le travail va commencer sous peu.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Commande acceptée',
            'message' => "Votre commande {$this->order->order_number} a été acceptée.",
            'icon' => 'check-circle',
            'url' => route('orders.show', $this->order),
        ];
    }
}
