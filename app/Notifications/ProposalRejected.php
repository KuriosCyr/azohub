<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProposalRejected extends Notification
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
        return (new MailMessage)
            ->subject('Votre proposition n\'a pas été retenue')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Le client a choisi une autre proposition pour la demande « {$this->proposal->serviceRequest->title} ».")
            ->line('Ne vous découragez pas, de nouvelles demandes sont publiées régulièrement.')
            ->action('Voir les demandes ouvertes', route('service-requests.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Proposition non retenue',
            'message' => 'Votre proposition pour « ' . $this->proposal->serviceRequest->title . ' » n\'a pas été retenue.',
            'icon' => 'x-circle',
            'url' => route('service-requests.index'),
        ];
    }
}
