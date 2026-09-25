<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// Envoyée au CLIENT dès que le paiement d'une commande est confirmé (le reçu FedaPay
// n'est pas sous notre contrôle et ne rassure pas sur la suite — celle-ci le fait).
class OrderConfirmed extends Notification
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
            ->subject('Commande confirmée — ' . $this->order->order_number)
            ->greeting('Merci ' . $notifiable->name . ' !')
            ->line("Votre paiement pour « {$this->order->display_title} » a bien été reçu, et votre commande {$this->order->order_number} est confirmée.")
            ->line("{$this->order->prestataire->name} a été prévenu(e) et va prendre votre commande en charge sous peu.")
            ->action('Suivre ma commande', route('orders.show', $this->order))
            ->line('Vous recevrez une notification à chaque étape : acceptation, livraison, etc.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Commande confirmée',
            'message' => "Votre commande {$this->order->order_number} est confirmée, en attente de prise en charge par le prestataire.",
            'icon' => 'check-circle',
            'url' => route('orders.show', $this->order),
        ];
    }
}
