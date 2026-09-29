<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

// Envoyée au client ET au prestataire quand une commande est auto-validée après expiration du
// délai (ValidateExpiredOrders — le client n'a pas réagi dans les 72h après livraison).
// Contrairement à une validation manuelle (cf. PaymentReleased), rien n'était envoyé dans ce
// cas : le prestataire découvrait le paiement crédité sans savoir pourquoi ni quand, et le
// client apprenait (au mieux) que sa commande était close en rouvrant la page lui-même.
class OrderAutoValidated extends Notification implements ShouldQueue
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
            ->subject('Commande auto-validée — ' . $this->order->order_number)
            ->greeting('Bonjour ' . $notifiable->name . ',');

        if ($this->forClient) {
            $mail->line("Vous n'avez pas répondu dans le délai imparti à la livraison de la commande {$this->order->order_number} (« {$this->order->display_title} »), elle a donc été automatiquement validée et le paiement libéré au prestataire.")
                ->line("Si le travail livré pose un problème, vous pouvez encore signaler un litige.")
                ->action('Voir la commande', route('orders.show', $this->order));
        } else {
            $mail->line("Le client n'a pas répondu dans le délai imparti à votre livraison de la commande {$this->order->order_number} (« {$this->order->display_title} »). Elle a été automatiquement validée : le paiement de " . number_format((float) $this->order->prestataire_amount, 0, ',', ' ') . ' FCFA a été crédité sur votre portefeuille.')
                ->action('Voir la commande', route('orders.show', $this->order));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Commande auto-validée',
            'message' => $this->forClient
                ? "La commande {$this->order->order_number} a été automatiquement validée, faute de réponse dans le délai."
                : number_format((float) $this->order->prestataire_amount, 0, ',', ' ') . " FCFA crédités automatiquement pour la commande {$this->order->order_number}.",
            'icon' => 'banknotes',
            'url' => route('orders.show', $this->order),
        ];
    }
}
