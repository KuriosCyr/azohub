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

    private function preview(): string
    {
        if (filled($this->message->message)) {
            return Str::limit($this->message->message, 80);
        }

        if ($this->message->custom_offer_id) {
            return 'Nouvelle offre personnalisée';
        }

        return 'Pièce(s) jointe(s)';
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
            ->line('« ' . $this->preview() . ' »')
            ->action('Répondre', route('conversations.show', $this->message->conversation_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouveau message',
            'message' => $this->message->sender->name . ' : ' . $this->preview(),
            'icon' => 'chat',
            'url' => route('conversations.show', $this->message->conversation_id),
        ];
    }
}
