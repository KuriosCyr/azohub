<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// "Relance" envoyée UNE FOIS au client dont la demande n'a reçu aucune proposition après
// quelques jours (voir RemindStaleServiceRequests, ServiceRequest::stale_reminded_at) — l'invite
// à ajuster sa demande plutôt que de la laisser sans retour jusqu'à expiration.
class ServiceRequestNoProposals extends Notification implements ShouldQueue
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
            ->subject('Aucune proposition pour le moment sur votre demande')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre demande « {$this->serviceRequest->title} » n'a encore reçu aucune proposition.")
            ->line('Un budget plus précis ou une description plus détaillée peuvent aider les prestataires à répondre.')
            ->action('Modifier ma demande', route('service-requests.show', $this->serviceRequest));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Aucune proposition pour le moment',
            'message' => 'Votre demande « ' . $this->serviceRequest->title . ' » n\'a encore reçu aucune proposition.',
            'icon' => 'clock',
            'url' => route('service-requests.show', $this->serviceRequest),
        ];
    }
}
