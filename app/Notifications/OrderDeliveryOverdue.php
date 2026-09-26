<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// Envoyée au client ET au prestataire une fois le délai de livraison dépassé (voir
// RemindOrderDeliveryOverdue). Contrairement à OrderDeadlineApproaching (avant l'échéance,
// prestataire seulement), rien n'était envoyé après coup : le client n'avait aucun moyen de
// savoir que sa commande était en retard sans penser à ouvrir la page lui-même.
class OrderDeliveryOverdue extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public bool $forClient)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Livraison en retard — ' . $this->order->order_number)
            ->greeting('Bonjour ' . $notifiable->name . ',');

        if ($this->forClient) {
            $mail->line("La livraison de la commande {$this->order->order_number} (« {$this->order->display_title} ») accuse du retard : elle était attendue le {$this->order->expected_delivery_at->translatedFormat('d M Y à H\hi')}.")
                ->line('Vous pouvez contacter le prestataire depuis la commande, ou signaler un litige si la situation ne se débloque pas.')
                ->action('Voir la commande', route('orders.show', $this->order));
        } else {
            $mail->line("La livraison de la commande {$this->order->order_number} (« {$this->order->display_title} ») est en retard : elle était attendue le {$this->order->expected_delivery_at->translatedFormat('d M Y à H\hi')}.")
                ->line('Livrez dès que possible pour éviter un litige de la part du client.')
                ->action('Livrer maintenant', route('orders.show', $this->order));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Livraison en retard',
            'message' => "La commande {$this->order->order_number} accuse du retard de livraison.",
            'icon' => 'exclamation-triangle',
            'url' => route('orders.show', $this->order),
        ];
    }
}
