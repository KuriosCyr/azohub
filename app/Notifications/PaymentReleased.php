<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReleased extends Notification
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
            ->subject('Paiement libéré')
            ->greeting('Bonne nouvelle, ' . $notifiable->name . ' !')
            ->line("Le client a validé la commande {$this->order->order_number}. Le paiement de " . number_format((float) $this->order->prestataire_amount, 0, ',', ' ') . ' FCFA a été crédité sur votre portefeuille.')
            ->action('Voir la commande', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Paiement libéré',
            'message' => number_format((float) $this->order->prestataire_amount, 0, ',', ' ') . ' FCFA crédités pour la commande ' . $this->order->order_number . '.',
            'icon' => 'banknotes',
            'url' => route('orders.show', $this->order),
        ];
    }
}
