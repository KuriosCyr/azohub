<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRejected extends Notification
{
    use Queueable;

    public function __construct(public Service $service)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre service n\'a pas été validé')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre service « {$this->service->title} » n'a pas pu être validé.")
            ->line('Motif : ' . ($this->service->moderation_note ?: 'non précisé.'))
            ->line('Vous pouvez le modifier ; il sera alors soumis à une nouvelle modération.')
            ->action('Modifier mon service', route('prestataire.services.edit', $this->service));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Service refusé',
            'message' => 'Votre service « ' . $this->service->title . ' » n\'a pas été validé. Motif : ' . ($this->service->moderation_note ?: 'non précisé.'),
            'icon' => 'x-circle',
            'url' => route('prestataire.services.edit', $this->service),
        ];
    }
}
