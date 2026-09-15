<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCancelled extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public string $cancelledByRole)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function initiator(): string
    {
        return $this->cancelledByRole === 'client' ? 'le client' : 'le prestataire';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Commande annulée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("La commande {$this->order->order_number} pour « {$this->order->service->title} » a été annulée par {$this->initiator()}.")
            ->line($this->order->cancellation_reason ? 'Raison : ' . $this->order->cancellation_reason : '')
            ->action('Voir la commande', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Commande annulée',
            'message' => "La commande {$this->order->order_number} a été annulée par {$this->initiator()}.",
            'icon' => 'x-circle',
            'url' => route('orders.show', $this->order),
        ];
    }
}
