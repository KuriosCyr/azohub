<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawalRequestPaid extends Notification
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
            ->subject('Votre retrait a été payé')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre demande de retrait de ' . number_format((float) $this->withdrawal->amount, 0, ',', ' ') . ' FCFA a été payée.')
            ->action('Voir mon portefeuille', route('prestataire.wallet'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Retrait payé',
            'message' => 'Votre retrait de ' . number_format((float) $this->withdrawal->amount, 0, ',', ' ') . ' FCFA a été payé.',
            'icon' => 'shield-check',
            'url' => route('prestataire.wallet'),
        ];
    }
}
