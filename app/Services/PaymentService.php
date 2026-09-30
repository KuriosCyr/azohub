<?php

namespace App\Services;

use App\Models\FedapayPayoutAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WithdrawalRequest;
use FedaPay\Customer;
use FedaPay\FedaPay;
use FedaPay\Payout;
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
    public function initiateForOrder(Order $order, string $paymentMethod, bool $useReferralCredit = false): string
    {
        // Réinitialise l'horloge d'expiration (ExpireStalePendingPayments se base sur
        // updated_at) à CHAQUE tentative de paiement, pas seulement à la création — audit
        // externe (2e audit) : sans ça, reprendre une commande proche de ses 24h via le bouton
        // "Payer" (PaymentController::initiate(), qui ne touche jamais la commande) pouvait la
        // voir annulée par la tâche planifiée pendant que le client était en train de payer,
        // laissant un paiement confirmé sans commande à activer.
        $order->touch();

        $order->applyReferralCredit($useReferralCredit);

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
    public function initiateForSubscription(Subscription $subscription, string $paymentMethod, bool $useReferralCredit = false): string
    {
        // Même principe que initiateForOrder() : réinitialise l'horloge d'expiration à chaque
        // tentative de paiement.
        $subscription->touch();

        $subscription->applyReferralCredit($useReferralCredit);

        $payer = $subscription->user;
        $plan = $subscription->plan;

        $transaction = Transaction::create([
            'description' => "Abonnement {$plan->name}" . ($subscription->billing_period === 'yearly' ? ' (annuel)' : '') . ' - Azohub',
            'amount' => (int) round((float) $subscription->total_charged),
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
            'amount' => $subscription->total_charged,
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
        // Sans secret configuré, la signature serait calculée avec une clé vide (donc falsifiable) :
        // on refuse tout webhook plutôt que d'en accepter un non authentifié.
        if ((string) config('services.fedapay.webhook_secret') === '') {
            throw new \RuntimeException('FEDAPAY_WEBHOOK_SECRET non configuré.');
        }

        $event = Webhook::constructEvent(
            $payload,
            $signature,
            (string) config('services.fedapay.webhook_secret')
        );

        if (!($event->object_id ?? null)) {
            return;
        }

        if (($event->entity ?? null) === 'payout') {
            $payout = Payout::retrieve((string) $event->object_id);

            $this->processPayoutUpdate((string) $payout->id, (string) $payout->status, $payout->last_error_message ?? null);

            return;
        }

        if (($event->entity ?? null) !== 'transaction') {
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

    // Déclenche un virement FedaPay réel pour un retrait déjà approuvé (le solde a été débité à
    // la demande, cf. PrestataireWallet::requestWithdrawal). Ne marque JAMAIS le retrait "payé"
    // elle-même : seule la confirmation FedaPay (webhook, cf. processPayoutUpdate()) le fait, un
    // envoi accepté par l'API n'étant pas une garantie que l'argent est bien arrivé.
    public function initiatePayout(WithdrawalRequest $withdrawal): void
    {
        // "Réclame" la demande AVANT le moindre appel réseau, sous verrou : deux clics
        // rapprochés (ou deux admins) ne peuvent plus tous les deux passer canRetryFedapayPayout()
        // et déclencher chacun un vrai virement FedaPay.
        $claimed = DB::transaction(function () use ($withdrawal) {
            $record = WithdrawalRequest::whereKey($withdrawal->id)->lockForUpdate()->first();

            if (!$record || !$record->canRetryFedapayPayout()) {
                return null;
            }

            $record->update(['fedapay_status' => 'initiating']);

            return $record;
        });

        if (!$claimed) {
            throw new \RuntimeException('Un virement FedaPay est déjà en cours pour cette demande.');
        }

        try {
            $customerId = $this->resolveFedapayCustomerId($claimed->prestataire);

            $payout = Payout::create([
                'customer' => ['id' => $customerId],
                'currency' => ['iso' => 'XOF'],
                'amount' => (int) round((float) $claimed->amount),
                // 'mtn' confirmé en sandbox (change une 500 muette en réponse propre) ; 'moov'/
                // 'celtiis' suivent la même convention que payment_method mais n'ont pas pu être
                // vérifiés (API Payout non autorisée sur ce compte au moment de l'écrire — voir
                // README). À revérifier dès l'activation par FedaPay.
                'mode' => match ($claimed->payment_method) {
                    'mtn_momo' => 'mtn',
                    'moov_money' => 'moov',
                    'celtiis_cash' => 'celtiis',
                    default => 'mtn',
                },
            ]);
        } catch (\Throwable $e) {
            // Rien n'a été créé côté FedaPay (aucun identifiant obtenu) : on sait avec certitude
            // qu'aucun argent n'a bougé, on peut donc réessayer sans risque.
            $claimed->update(['fedapay_status' => null]);

            throw $e;
        }

        // Enregistré tout de suite, AVANT sendNow() — pas après : si l'envoi échoue ensuite
        // (coupure réseau, délai dépassé...), l'identifiant reste en base et bloque toute
        // nouvelle tentative automatique tant qu'on ne sait pas avec certitude si l'argent est
        // parti. Seul le webhook FedaPay (confirmation ou échec) ou une vérification manuelle
        // sur le tableau de bord FedaPay (WithdrawalRequest::clearAmbiguousFedapayAttempt())
        // peut ensuite débloquer la demande — jamais un nouvel essai automatique, qui risquerait
        // un double envoi si le premier avait en réalité réussi.
        $sent = $claimed->markFedapayPayoutSent((string) $payout->id);

        // La demande a été débloquée manuellement pendant qu'on préparait ce virement (audit
        // externe — 3e audit, voir markFedapayPayoutSent()) : on N'ENVOIE PAS cet argent — un
        // autre essai a pu être déclenché entre-temps, et sendNow() ici causerait un vrai double
        // paiement. Le virement FedaPay créé (mais jamais envoyé) reste orphelin côté FedaPay,
        // sans impact financier ; on alerte l'admin pour qu'il vérifie qu'aucun double paiement
        // n'a eu lieu via l'autre essai.
        if (!$sent) {
            AdminNotifier::actionRequired(
                'Virement FedaPay créé mais volontairement non envoyé (double essai détecté)',
                "Un virement FedaPay ({$payout->id}, {$claimed->amount} FCFA pour {$claimed->prestataire->name}) a été créé mais PAS envoyé : la demande de retrait #{$claimed->id} a été débloquée manuellement pendant sa préparation, signe qu'un autre essai est peut-être en cours ou a déjà eu lieu. Vérifiez sur le tableau de bord FedaPay avant toute nouvelle action, pour écarter un double paiement.",
                route('filament.admin.resources.withdrawal-requests.index'),
            );

            return;
        }

        $phone = preg_replace('/\D/', '', (string) $claimed->phone_number);

        $payout->sendNow([
            'phone_number' => [
                'number' => $phone,
                'country' => 'bj',
            ],
        ]);
    }

    // Un prestataire n'a qu'un seul client FedaPay, créé au premier virement automatisé et
    // réutilisé ensuite (évite d'en créer un nouveau à chaque retrait).
    protected function resolveFedapayCustomerId(User $prestataire): string
    {
        if ($prestataire->fedapay_customer_id) {
            return $prestataire->fedapay_customer_id;
        }

        $nameParts = explode(' ', trim($prestataire->name), 2);

        try {
            $customer = Customer::create([
                'firstname' => $nameParts[0] ?: $prestataire->name,
                'lastname' => $nameParts[1] ?? $nameParts[0],
                'email' => $prestataire->email,
                'phone_number' => [
                    'number' => preg_replace('/\D/', '', (string) $prestataire->phone),
                    'country' => 'bj',
                ],
            ]);
        } catch (\FedaPay\Error\Base $e) {
            // Confirmé en sandbox : FedaPay refuse un email déjà utilisé par un client existant
            // côté FedaPay (ex. le prestataire a déjà un compte FedaPay pour une autre raison).
            // Cette erreur n'a rien à voir avec un problème réseau malgré le nom de la classe
            // ("ApiConnection") — la réponse HTTP (400) et son corps JSON le confirment. Dans ce
            // cas, on retrouve et réutilise le client existant au lieu d'échouer.
            $errors = $e->getJsonBody()['errors'] ?? [];

            if (!isset($errors['email'])) {
                throw $e;
            }

            $existingId = $this->findFedapayCustomerIdByEmail($prestataire->email);

            if ($existingId === null) {
                throw $e;
            }

            $prestataire->update(['fedapay_customer_id' => $existingId]);

            return $existingId;
        }

        $prestataire->update(['fedapay_customer_id' => (string) $customer->id]);

        return (string) $customer->id;
    }

    // Le paramètre 'email' de Customer::all() ne filtre pas côté serveur (vérifié en sandbox :
    // renvoie tous les clients quel que soit le filtre) — comparaison manuelle sur les pages
    // renvoyées, plafonnée pour ne jamais tourner indéfiniment sur un très gros historique.
    protected function findFedapayCustomerIdByEmail(string $email): ?string
    {
        $page = 1;

        while ($page <= 20) {
            $result = Customer::all(['per_page' => 100, 'page' => $page]);

            foreach ($result->customers ?? [] as $customer) {
                if (strcasecmp((string) $customer->email, $email) === 0) {
                    return (string) $customer->id;
                }
            }

            if (empty($result->meta->next_page ?? null)) {
                break;
            }

            $page++;
        }

        return null;
    }

    // Traite la confirmation (ou l'échec) d'un virement, via webhook. $lastErrorMessage vient de
    // FedaPay (last_error_message) quand disponible ; à défaut, message générique.
    public function processPayoutUpdate(string $payoutId, string $status, ?string $lastErrorMessage): void
    {
        $withdrawal = WithdrawalRequest::where('fedapay_payout_id', $payoutId)->first();

        if (!$withdrawal) {
            // Ne retourne plus en silence (audit externe) : cet identifiant a pu appartenir à
            // une demande depuis débloquée manuellement (WithdrawalRequest::
            // clearAmbiguousFedapayAttempt() vide fedapay_payout_id sans supprimer l'historique,
            // voir FedapayPayoutAttempt) — si elle a ensuite été repayée autrement, ce webhook
            // signale potentiellement un double paiement. Toujours alerter l'admin plutôt que de
            // laisser ça invisible.
            // Seul 'sent'/'failed' déclenche une alerte (audit externe — 3e audit) : FedaPay
            // envoie plusieurs webhooks par virement (pending, processing, sent...) — sans ce
            // filtre, un même virement débloqué/historique déclenchait une alerte distincte à
            // chaque étape intermédiaire, avant même de savoir si l'argent est vraiment parti.
            if (!in_array($status, ['sent', 'failed'], true)) {
                return;
            }

            $historicalAttempt = FedapayPayoutAttempt::where('fedapay_payout_id', $payoutId)->first();

            AdminNotifier::actionRequired(
                'Webhook FedaPay reçu pour un virement non reconnu',
                $historicalAttempt
                    ? "Le virement {$payoutId} (statut FedaPay : {$status}) correspondait à la demande de retrait #{$historicalAttempt->withdrawal_request_id}, mais n'y est plus rattaché (débloquée manuellement depuis). Vérifiez qu'aucun double paiement n'a eu lieu."
                    : "Le virement {$payoutId} (statut FedaPay : {$status}) ne correspond à aucune demande de retrait connue.",
                route('filament.admin.resources.withdrawal-requests.index'),
            );

            return;
        }

        if ($status === 'sent') {
            $withdrawal->confirmFedapayPayout();

            return;
        }

        // Volontairement strict : seul 'sent' est traité comme un succès, seul 'failed' comme un
        // échec confirmé. Un statut encore intermédiaire (pending...) ne déclenche rien — mieux
        // vaut attendre une confirmation nette qu'agir sur un état encore incertain, l'argent
        // restant de toute façon débité côté FedaPay tant qu'aucun webhook net n'est arrivé.
        if ($status === 'failed') {
            $withdrawal->failFedapayPayout($lastErrorMessage ?? 'Raison non précisée par FedaPay.');
        }
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
