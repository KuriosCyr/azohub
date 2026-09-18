<?php

namespace App\Notifications;

use App\Models\ConversationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class NewConversationMessage extends Notification
{
    use Queueable;

    public function __construct(public ConversationMessage $message)
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
            ->line("{$sender->name} vous a envoyé un message sur Azohub.")
            ->line('« ' . Str::limit($this->message->message ?? 'Nouvelle offre personnalisée', 150) . ' »')
            ->action('Répondre', route('conversations.show', $this->message->conversation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouveau message',
            'message' => $this->message->sender->name . ' : ' . Str::limit($this->message->message ?? 'Nouvelle offre personnalisée', 80),
            'icon' => 'chat',
            'url' => route('conversations.show', $this->message->conversation_id),
        ];
    }
}
