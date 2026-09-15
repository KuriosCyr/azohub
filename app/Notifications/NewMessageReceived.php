<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class NewMessageReceived extends Notification
{
    use Queueable;

    public function __construct(public Message $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sender = $this->message->sender;

        return (new MailMessage)
            ->subject('Nouveau message de ' . $sender->name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("{$sender->name} vous a envoyé un message concernant la commande {$this->message->order->order_number}.")
            ->line('« ' . Str::limit($this->message->message, 150) . ' »')
            ->action('Répondre', route('orders.show', $this->message->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouveau message',
            'message' => $this->message->sender->name . ' : ' . Str::limit($this->message->message, 80),
            'icon' => 'chat',
            'url' => route('orders.show', $this->message->order),
        ];
    }
}
