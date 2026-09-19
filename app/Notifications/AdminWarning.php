<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminWarning extends Notification
{
    use Queueable;

    public function __construct(public string $subject, public string $body)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->body)
            ->salutation('L\'équipe Azohub');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->subject,
            'message' => $this->body,
            'icon' => 'exclamation-triangle',
            'url' => route('profile.edit'),
        ];
    }
}
