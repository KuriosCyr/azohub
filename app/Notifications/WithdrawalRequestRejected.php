<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalRequestRejected extends Notification
{
    use Queueable;

    public function __construct(public WithdrawalRequest $withdrawal)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre demande de retrait a été refusée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre demande de retrait de ' . number_format((float) $this->withdrawal->amount, 0, ',', ' ') . ' FCFA a été refusée pour la raison suivante :')
            ->line($this->withdrawal->admin_note ?? 'Non précisée.')
            ->line('Le montant a été recrédité sur votre solde disponible.')
            ->action('Voir mon portefeuille', route('prestataire.wallet'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Retrait refusé',
            'message' => 'Votre retrait de ' . number_format((float) $this->withdrawal->amount, 0, ',', ' ') . ' FCFA a été refusé. Le montant a été recrédité.',
            'icon' => 'x-circle',
            'url' => route('prestataire.wallet'),
        ];
    }
}
