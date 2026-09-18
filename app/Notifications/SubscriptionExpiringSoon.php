<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiringSoon extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre abonnement ' . $this->subscription->plan->name . ' expire bientôt')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre abonnement au plan « {$this->subscription->plan->name} » expire le {$this->subscription->ends_at->format('d/m/Y')}.")
            ->line('Renouvelez-le dès maintenant pour continuer à profiter de votre commission réduite et de votre limite de services.')
            ->action('Renouveler mon abonnement', route('prestataire.subscription'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Abonnement bientôt expiré',
            'message' => 'Votre abonnement « ' . $this->subscription->plan->name . ' » expire le ' . $this->subscription->ends_at->format('d/m/Y') . '. Pensez à le renouveler.',
            'icon' => 'exclamation-triangle',
            'url' => route('prestataire.subscription'),
        ];
    }
}
