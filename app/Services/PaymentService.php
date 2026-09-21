<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use FedaPay\Webhook;
use Illuminate\Support\Facades\DB;

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
            // Le binding de Order utilise order_number : passer le modèle (et non l'id) sinon le
            // retour du client depuis FedaPay tombait sur une 404.
            'callback_url' => route('payments.callback', ['order' => $order]),
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
     * Crée une transaction FedaPay pour un abonnement prestataire et renvoie l'URL
     * de paiement vers laquelle rediriger le prestataire.
     */
    public function initiateForSubscription(Subscription $subscription, string $paymentMethod): string
    {
        $payer = $subscription->user;
        $plan = $subscription->plan;

        $transaction = Transaction::create([
            'description' => "Abonnement {$plan->name}" . ($subscription->billing_period === 'yearly' ? ' (annuel)' : '') . ' - Azohub',
            'amount' => (int) round($plan->priceFor($subscription->billing_period)),
            'currency' => ['iso' => 'XOF'],
            'callback_url' => route('payments.subscription-callback', ['subscription' => $subscription->id]),
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
            'subscription_id' => $subscription->id,
            'user_id' => $payer->id,
            'transaction_id' => (string) $transaction->id,
            'payment_method' => $paymentMethod,
            'phone_number' => $payer->phone,
            'amount' => $plan->priceFor($subscription->billing_period),
            'status' => 'pending',
            'type' => 'subscription',
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

        $transaction = $this->fetchTransaction((string) $event->object_id);

        $this->processTransactionUpdate(
            (string) $transaction->id,
            $transaction->wasPaid(),
            (string) $transaction->status,
            $transaction->__toJSON()
        );
    }

    /**
     * Interroge FedaPay sur l'état réel d'un paiement encore en attente et le traite.
     * Appelé au retour du client depuis la page de paiement : ça fonctionne même si le
     * webhook n'arrive pas (ex. en local, où FedaPay ne peut pas joindre le site) ou tarde.
     * Idempotent : processTransactionUpdate() ignore un paiement déjà traité.
     */
    public function syncPayment(Payment $payment): void
    {
        if ($payment->status !== 'pending') {
            return;
        }

        $transaction = $this->fetchTransaction((string) $payment->transaction_id);

        $this->processTransactionUpdate(
            (string) $transaction->id,
            $transaction->wasPaid(),
            (string) $transaction->status,
            $transaction->__toJSON()
        );
    }

    protected function fetchTransaction(string $id)
    {
        return Transaction::retrieve($id);
    }

    // Verrouillé + gardé par le statut : FedaPay peut livrer le même événement
    // plusieurs fois (retries), et sans ça deux livraisons concurrentes liraient
    // toutes les deux "pending" avant qu'aucune n'ait écrit, traitant deux fois
    // le paiement (ex. double renouvellement d'abonnement). Extrait de
    // handleWebhook() pour rester testable sans dépendre du SDK FedaPay.
    public function processTransactionUpdate(string $transactionId, bool $wasPaid, string $status, ?string $gatewayResponse): void
    {
        DB::transaction(function () use ($transactionId, $wasPaid, $status, $gatewayResponse) {
            $payment = Payment::where('transaction_id', $transactionId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->status !== 'pending') {
                return;
            }

            if ($wasPaid) {
                $payment->markAsPaid($gatewayResponse);
            } elseif (in_array($status, ['declined', 'canceled'], true)) {
                $payment->markAsFailed($gatewayResponse);
            }
        });
    }
}
