<?php

namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DisputeOpened extends Notification
{
    use Queueable;

    public function __construct(public Dispute $dispute)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->dispute->order;

        return (new MailMessage)
            ->subject('Un litige a été ouvert sur votre commande')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("{$this->dispute->openedBy->name} a ouvert un litige concernant la commande {$order->order_number}.")
            ->line('Notre équipe va examiner la situation. La commande reste bloquée en attendant la résolution du litige.')
            ->action('Voir la commande', route('orders.show', $order));
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->dispute->order;

        return [
            'title' => 'Litige ouvert',
            'message' => "Un litige a été ouvert sur la commande {$order->order_number}.",
            'icon' => 'exclamation-triangle',
            'url' => route('orders.show', $order),
        ];
    }
}
