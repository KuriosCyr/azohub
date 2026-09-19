<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RevisionRequested extends Notification
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
            ->subject('Le client demande une révision')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Le client demande une révision sur la commande {$this->order->order_number} pour « {$this->order->display_title} ».")
            ->line($this->order->revision_notes ? 'Détails : ' . $this->order->revision_notes : '')
            ->action('Voir la demande', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Révision demandée',
            'message' => "Le client demande une révision sur la commande {$this->order->order_number}.",
            'icon' => 'pencil-square',
            'url' => route('orders.show', $this->order),
        ];
    }
}
