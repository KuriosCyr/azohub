<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// PAS de ShouldQueue ici, volontairement — seule exception à la règle "toutes les
// notifications en file" posée ailleurs dans ce projet : l'utilisateur est sur l'écran de
// saisie du code EN CE MOMENT, en attente. Si le worker est momentanément arrêté ou en retard,
// personne ne pourrait plus se connecter du tout (au lieu d'un simple email de chat en retard
// de quelques secondes). L'authentification ne doit pas dépendre de la santé du worker.
class TwoFactorCode extends Notification
{
    use Queueable;

    public function __construct(public string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code de connexion Azohub : ' . $this->code)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Voici votre code de connexion à usage unique :')
            ->line(new \Illuminate\Support\HtmlString('<p style="font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align: center;">' . $this->code . '</p>'))
            ->line('Ce code expire dans 10 minutes.')
            ->line('Si vous n\'êtes pas à l\'origine de cette tentative de connexion, ignorez cet email et envisagez de changer votre mot de passe.');
    }
}
