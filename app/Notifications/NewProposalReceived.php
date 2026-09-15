<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewProposalReceived extends Notification
{
    use Queueable;

    public function __construct(public Proposal $proposal)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $prestataire = $this->proposal->prestataire;

        return (new MailMessage)
            ->subject('Nouvelle proposition reçue')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("{$prestataire->name} a répondu à votre demande « {$this->proposal->serviceRequest->title} » avec une proposition à " . number_format((float) $this->proposal->proposed_price, 0, ',', ' ') . ' FCFA.')
            ->action('Voir la proposition', route('service-requests.show', $this->proposal->serviceRequest));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle proposition',
            'message' => $this->proposal->prestataire->name . ' a proposé ' . number_format((float) $this->proposal->proposed_price, 0, ',', ' ') . ' FCFA pour « ' . $this->proposal->serviceRequest->title . ' ».',
            'icon' => 'clipboard',
            'url' => route('service-requests.show', $this->proposal->serviceRequest),
        ];
    }
}
