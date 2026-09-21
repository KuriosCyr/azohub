<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceApproved extends Notification
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
            ->subject('Votre service est en ligne')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre service « {$this->service->title} » a été validé par notre équipe. Il est maintenant visible par les clients.")
            ->action('Voir mon service', route('services.show', $this->service->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Service validé',
            'message' => 'Votre service « ' . $this->service->title . ' » est en ligne.',
            'icon' => 'check-circle',
            'url' => route('services.show', $this->service->slug),
        ];
    }
}
