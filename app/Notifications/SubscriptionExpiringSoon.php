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
        $renewUrl = route('prestataire.subscription', $this->subscription->auto_renew ? ['renew' => $this->subscription->subscription_plan_id] : []);

        $mail = (new MailMessage)
            ->subject('Votre abonnement ' . $this->subscription->plan->name . ' expire bientôt')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Votre abonnement au plan « {$this->subscription->plan->name} » expire le {$this->subscription->ends_at->format('d/m/Y')}.")
            ->line('Renouvelez-le dès maintenant pour continuer à profiter de votre commission réduite et de votre limite de services.');

        if (!$this->subscription->auto_renew) {
            $mail->line('Astuce : activez le rappel de renouvellement rapide depuis votre page d\'abonnement pour un lien de renouvellement pré-rempli la prochaine fois.');
        }

        return $mail->action($this->subscription->auto_renew ? 'Renouveler en un clic' : 'Renouveler mon abonnement', $renewUrl);
    }

    public function toArray(object $notifiable): array
    {
        $renewUrl = route('prestataire.subscription', $this->subscription->auto_renew ? ['renew' => $this->subscription->subscription_plan_id] : []);

        return [
            'title' => 'Abonnement bientôt expiré',
            'message' => 'Votre abonnement « ' . $this->subscription->plan->name . ' » expire le ' . $this->subscription->ends_at->format('d/m/Y') . '. Pensez à le renouveler.',
            'icon' => 'exclamation-triangle',
            'url' => $renewUrl,
        ];
    }
}
