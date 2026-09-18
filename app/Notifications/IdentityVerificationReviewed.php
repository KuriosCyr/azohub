<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class IdentityVerificationReviewed extends Notification
{
    use Queueable;

    public function __construct(public bool $approved, public ?string $reason = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->greeting('Bonjour ' . $notifiable->name . ',');

        if ($this->approved) {
            return $mail
                ->subject('Votre identité a été vérifiée')
                ->line('Bonne nouvelle : votre pièce d\'identité a été validée. Le badge "Vérifié" est maintenant visible sur votre profil.')
                ->action('Voir mon profil', route('profile.edit'));
        }

        return $mail
            ->subject('Votre pièce d\'identité n\'a pas été validée')
            ->line('Votre pièce d\'identité n\'a pas pu être validée pour la raison suivante :')
            ->line($this->reason ?? 'Document illisible ou non conforme.')
            ->line('Vous pouvez soumettre un nouveau document depuis votre profil.')
            ->action('Soumettre un nouveau document', route('profile.edit'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->approved ? 'Identité vérifiée' : 'Vérification refusée',
            'message' => $this->approved
                ? 'Votre pièce d\'identité a été validée.'
                : 'Votre pièce d\'identité n\'a pas été validée : ' . ($this->reason ?? ''),
            'icon' => $this->approved ? 'shield-check' : 'x-circle',
            'url' => route('profile.edit'),
        ];
    }
}
