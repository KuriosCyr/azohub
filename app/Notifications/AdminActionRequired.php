<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notification générique envoyée aux administrateurs quand un élément nécessite leur action
// (litige, signalement, vérification d'identité, demande de retrait, service en attente...).
// Canal 'mail' uniquement : le canal 'database' de Laravel écrirait dans `notifications` un
// format différent de celui que la cloche Filament attend — voir AdminNotifier, qui envoie
// séparément une Filament\Notifications\Notification (canal database) au bon format.
class AdminActionRequired extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $url,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->message)
            ->action('Voir dans l\'administration', $this->url);
    }
}
