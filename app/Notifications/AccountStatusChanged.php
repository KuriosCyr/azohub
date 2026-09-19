<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public bool $activated, public ?string $reason = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->greeting('Bonjour ' . $notifiable->name . ',');

        if ($this->activated) {
            return $mail
                ->subject('Votre compte Azohub a été réactivé')
                ->line('Bonne nouvelle : votre compte a été réactivé par notre équipe. Vous pouvez de nouveau vous connecter normalement.')
                ->action('Se connecter', route('login'));
        }

        return $mail
            ->subject('Votre compte Azohub a été désactivé')
            ->line('Votre compte a été désactivé par notre équipe pour la raison suivante :')
            ->line($this->reason ?? 'Non précisée.')
            ->line('Si vous pensez qu\'il s\'agit d\'une erreur, contactez notre support.')
            ->action('Nous contacter', route('contact'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->activated ? 'Compte réactivé' : 'Compte désactivé',
            'message' => $this->activated
                ? 'Votre compte a été réactivé.'
                : 'Votre compte a été désactivé : ' . ($this->reason ?? 'motif non précisé.'),
            'icon' => $this->activated ? 'shield-check' : 'x-circle',
            'url' => route('login'),
        ];
    }
}
