<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionActivated extends Notification
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
            ->subject('Votre abonnement ' . $this->subscription->plan->name . ' est actif')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre abonnement au plan « {$this->subscription->plan->name} » est maintenant actif jusqu'au {$this->subscription->ends_at->format('d/m/Y')}.")
            ->line('Commission Azohub : ' . number_format((float) $this->subscription->plan->commission_rate, 0) . '%.')
            ->action('Voir mon abonnement', route('prestataire.subscription'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Abonnement activé',
            'message' => 'Votre abonnement « ' . $this->subscription->plan->name . ' » est actif jusqu\'au ' . $this->subscription->ends_at->format('d/m/Y') . '.',
            'icon' => 'shield-check',
            'url' => route('prestataire.subscription'),
        ];
    }
}
