<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceRequestExpired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ServiceRequest $serviceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre demande a expiré')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre demande « {$this->serviceRequest->title} » a expiré sans avoir été pourvue.")
            ->line('Vous pouvez publier une nouvelle demande à tout moment.')
            ->action('Publier une nouvelle demande', route('service-requests.create'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Demande expirée',
            'message' => 'Votre demande « ' . $this->serviceRequest->title . ' » a expiré.',
            'icon' => 'clock',
            'url' => route('service-requests.create'),
        ];
    }
}
