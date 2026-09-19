<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderDelivered extends Notification
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
            ->subject('Votre commande a été livrée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Le prestataire a livré votre commande {$this->order->order_number} pour « {$this->order->display_title} ».")
            ->line('Merci de vérifier le travail et de le valider pour libérer le paiement.')
            ->action('Vérifier et valider', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Commande livrée',
            'message' => "La commande {$this->order->order_number} a été livrée, en attente de votre validation.",
            'icon' => 'upload',
            'url' => route('orders.show', $this->order),
        ];
    }
}
