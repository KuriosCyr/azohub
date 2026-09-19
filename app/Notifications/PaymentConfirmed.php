<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentConfirmed extends Notification
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
            ->subject('Nouvelle commande payée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Vous avez reçu une nouvelle commande payée : {$this->order->order_number} pour « {$this->order->display_title} ».")
            ->line('Acceptez-la rapidement pour commencer le travail.')
            ->action('Voir la commande', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle commande payée',
            'message' => "Commande {$this->order->order_number} payée, en attente de votre acceptation.",
            'icon' => 'card',
            'url' => route('orders.show', $this->order),
        ];
    }
}
