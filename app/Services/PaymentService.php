<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use FedaPay\Webhook;

class PaymentService
{
    public function __construct()
    {
        FedaPay::setApiKey((string) config('services.fedapay.secret_key'));
        FedaPay::setEnvironment((string) config('services.fedapay.environment'));
    }

    /**
     * Crée une transaction FedaPay pour une commande et renvoie l'URL de paiement
     * vers laquelle rediriger le client.
     */
    public function initiateForOrder(Order $order, string $paymentMethod): string
    {
        $payer = $order->client;

        $transaction = Transaction::create([
            'description' => "Paiement commande {$order->order_number} - Azohub",
            'amount' => (int) round((float) $order->total_charged),
            'currency' => ['iso' => 'XOF'],
            'callback_url' => route('payments.callback', ['order' => $order->id]),
            'customer' => [
                'firstname' => $payer->name,
                'email' => $payer->email,
                'phone_number' => [
                    'number' => preg_replace('/\D/', '', (string) $payer->phone),
                    'country' => 'bj',
                ],
            ],
        ]);

        $token = $transaction->generateToken();

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $payer->id,
            'transaction_id' => (string) $transaction->id,
            'payment_method' => $paymentMethod,
            'phone_number' => $payer->phone,
            'amount' => $order->total_charged,
            'status' => 'pending',
            'type' => 'order_payment',
            'gateway_reference' => $transaction->reference ?? null,
        ]);

        return $token->url;
    }

    /**
     * Vérifie et traite un webhook FedaPay. Lève une exception si la signature
     * est invalide ; ne fait rien si l'événement ne concerne pas une transaction connue.
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        $event = Webhook::constructEvent(
            $payload,
            $signature,
            (string) config('services.fedapay.webhook_secret')
        );

        if (($event->entity ?? null) !== 'transaction' || !($event->object_id ?? null)) {
            return;
        }

        $transaction = Transaction::retrieve($event->object_id);

        $payment = Payment::where('transaction_id', (string) $transaction->id)->first();

        if (!$payment || $payment->status !== 'pending') {
            return;
        }

        if ($transaction->wasPaid()) {
            $payment->markAsPaid($transaction->__toJSON());
        } elseif (in_array($transaction->status, ['declined', 'canceled'], true)) {
            $payment->markAsFailed($transaction->__toJSON());
        }
    }
}
