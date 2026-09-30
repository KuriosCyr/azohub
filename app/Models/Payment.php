<?php

namespace App\Models;

use App\Notifications\OrderConfirmed;
use App\Notifications\PaymentConfirmed;
use App\Notifications\ProposalRejected;
use App\Notifications\SubscriptionActivated;
use App\Services\AdminNotifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'subscription_id',
        'user_id',
        'transaction_id',
        'payment_method',
        'phone_number',
        'amount',
        'refund_amount_due',
        'status',
        'type',
        'gateway_response',
        'gateway_reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount_due' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Marquer comme payé (appelé par PaymentService après confirmation FedaPay)
    public function markAsPaid(?string $gatewayResponse = null)
    {
        $this->paid_at = now();
        if ($gatewayResponse !== null) {
            $this->gateway_response = $gatewayResponse;
        }

        if ($this->order_id) {
            // Verrouille la commande pour TOUTE la décision (vérifier son statut ET l'activer) :
            // sans ça, une annulation concurrente (OrderController::cancel(), elle-même
            // verrouillée) peut s'intercaler entre la simple lecture du statut et l'activation,
            // "ressuscitant" en 'paid' une commande tout juste annulée (audit externe — fenêtre
            // de quelques millisecondes, mais avec un vrai impact financier si elle se produit).
            DB::transaction(function () {
                $order = Order::whereKey($this->order_id)->lockForUpdate()->first();

                // Le client a ouvert la page FedaPay, puis annulé la commande sur Azohub (ou une
                // autre proposition/offre a été choisie entre-temps, cf. ProposalAccept), mais a
                // quand même terminé le paiement dans l'autre onglet — ou un double paiement
                // (deux onglets, deux transactions réussies sur la même commande) confirme un
                // deuxième paiement alors que le premier a déjà financé la commande. Dans les
                // deux cas, FedaPay a bel et bien encaissé l'argent : on ne le laisse jamais
                // "success" sans suite (ce qui le rendrait invisible côté Azohub, sans
                // remboursement prévu), on le marque à rembourser manuellement et on alerte
                // l'admin. On ne touche pas à la commande elle-même : son statut actuel est déjà
                // ce qu'il doit être (annulée, ou déjà financée par un autre paiement) — la
                // ressusciter ici serait pire que ne rien faire.
                if (!$order || $order->status !== 'pending_payment') {
                    $this->status = 'refund_pending';
                    $this->refund_amount_due = (float) $this->amount;
                    $this->save();

                    if ($order) {
                        AdminNotifier::actionRequired(
                            'Paiement reçu sur une commande non payable',
                            "Le paiement de {$this->amount} FCFA (transaction {$this->transaction_id}) pour la commande {$order->order_number} a été confirmé par FedaPay, mais la commande n'est plus en attente de paiement (statut actuel : {$order->status}). Remboursement à traiter manuellement.",
                            route('filament.admin.resources.orders.edit', $order),
                        );
                    }

                    return;
                }

                // Le montant réellement confirmé par FedaPay doit correspondre à ce que la
                // commande demande MAINTENANT (audit externe — 2e audit, suggestion de clôture) :
                // OrderCreate réutilise une commande pending_payment plutôt que d'en recréer une
                // à chaque tentative — un onglet FedaPay resté ouvert depuis AVANT un changement
                // de prix (ex. service repassé en modération puis re-publié à un autre tarif)
                // pourrait sinon activer la commande à l'ancien prix, avec un escrow insuffisant
                // pour couvrir ce qui est réellement dû au prestataire. Tolérance de 1 FCFA pour
                // l'arrondi.
                if (abs((float) $this->amount - (float) $order->total_charged) > 1) {
                    $this->status = 'refund_pending';
                    $this->refund_amount_due = (float) $this->amount;
                    $this->save();

                    AdminNotifier::actionRequired(
                        'Paiement reçu ne correspondant plus au montant de la commande',
                        "Le paiement de {$this->amount} FCFA (transaction {$this->transaction_id}) pour la commande {$order->order_number} a été confirmé par FedaPay, mais la commande demande maintenant {$order->total_charged} FCFA (le prix ou une réduction a changé depuis). Remboursement à traiter manuellement.",
                        route('filament.admin.resources.orders.edit', $order),
                    );

                    return;
                }

                $this->status = 'success';
                $this->save();

                $this->activatePaidOrder($order);
            });
        } elseif ($this->subscription_id) {
            // Même principe que la branche commande ci-dessus (audit externe — 2e audit) :
            // l'abonnement peut avoir été annulé entre-temps (expiré après 24h, ou paiement
            // marqué échoué — voir Subscription::cancelAbandoned()) avant qu'une confirmation
            // FedaPay tardive n'arrive. Avant ce correctif, rien ne gérait ce cas précis : le
            // paiement restait "success" pour toujours, invisible, sans remboursement prévu.
            DB::transaction(function () {
                $subscription = Subscription::whereKey($this->subscription_id)->lockForUpdate()->first();

                if (!$subscription || $subscription->status !== 'pending') {
                    $this->status = 'refund_pending';
                    $this->refund_amount_due = (float) $this->amount;
                    $this->save();

                    if ($subscription) {
                        AdminNotifier::actionRequired(
                            'Paiement d\'abonnement reçu, abonnement non activable',
                            "Le paiement de {$this->amount} FCFA (transaction {$this->transaction_id}) pour l'abonnement #{$subscription->id} de {$subscription->user->name} a été confirmé par FedaPay, mais l'abonnement n'est plus en attente (statut actuel : {$subscription->status}). Remboursement à traiter manuellement.",
                            route('filament.admin.resources.subscriptions.edit', $subscription),
                        );
                    }

                    return;
                }

                $this->status = 'success';
                $this->save();

                $this->activatePendingSubscription($subscription);
            });
        } else {
            $this->status = 'success';
            $this->save();
        }
    }

    // La commande passe en "paid" et le paiement reste bloqué en escrow jusqu'à validation de
    // la livraison par le client (cf. Order::releasePayment()).
    private function activatePaidOrder(Order $order): void
    {
        // Compte à rebours de livraison : démarre tout de suite pour une commande directe
        // (le service et son délai sont déjà définis) — pour une commande négociée, il
        // démarre seulement quand le prestataire accepte (cf. OrderController::accept()),
        // le travail n'ayant pas encore été formellement cadré avant ça.
        $expectedDeliveryAt = (!$order->isNegotiated() && $order->delivery_time)
            ? now()->addDays($order->delivery_time)
            : null;

        $order->update([
            'status' => 'paid',
            'payment_status' => 'held',
            'expected_delivery_at' => $expectedDeliveryAt,
        ]);

        $order->prestataire->notify(new PaymentConfirmed($order));
        $order->client->notify(new OrderConfirmed($order));

        $this->finalizeNegotiatedOrder($order);
    }

    // Un seul abonnement actif à la fois : celui-ci remplace tout abonnement en cours.
    // Un paiement (carte notamment) peut rester bloqué côté FedaPay puis se confirmer
    // très en retard, après que l'utilisateur a entre-temps déjà payé et activé un autre
    // plan pendant que celui-ci restait "pending" en attente. Sans ce garde-fou, cette
    // confirmation tardive écraserait l'abonnement actif actuel avec un choix abandonné
    // depuis longtemps — donc on vérifie qu'aucun abonnement plus récent n'est déjà actif
    // avant de traiter celui-ci.
    private function activatePendingSubscription(Subscription $subscription): void
    {
        $supersededByNewerActive = Subscription::where('user_id', $subscription->user_id)
            ->where('status', 'active')
            ->where('created_at', '>', $subscription->created_at)
            ->exists();

        if ($supersededByNewerActive) {
            // Un simple Log::warning() ne suffisait pas (audit externe) : FedaPay a bel et bien
            // encaissé cet argent pour un abonnement qui ne sera jamais activé — même traitement
            // que l'argent orphelin côté commande (voir plus haut dans markAsPaid()) : marqué à
            // rembourser manuellement, et l'admin est alerté au lieu de ne rien voir du tout.
            $this->update(['status' => 'refund_pending', 'refund_amount_due' => (float) $this->amount]);

            AdminNotifier::actionRequired(
                'Paiement d\'abonnement reçu en retard, abonnement déjà remplacé',
                "Le paiement de {$this->amount} FCFA (transaction {$this->transaction_id}) pour l'abonnement #{$subscription->id} de {$subscription->user->name} a été confirmé par FedaPay, mais un abonnement plus récent est déjà actif pour cet utilisateur. Remboursement à traiter manuellement.",
                route('filament.admin.resources.subscriptions.edit', $subscription),
            );

            return;
        }

        $others = Subscription::where('user_id', $subscription->user_id)
            ->where('id', '!=', $subscription->id)
            ->where('status', 'active');

        // Le temps restant sur l'abonnement remplacé est reporté sur le nouveau, que ce
        // soit un renouvellement anticipé du même plan ou une montée en gamme (Pro ->
        // Premium) : PrestataireSubscription::choosePlan() ne laisse jamais arriver
        // jusqu'ici un changement qui ferait perdre du temps déjà payé (une baisse de
        // gamme y est bloquée avant le paiement), donc tout ce qui atteint ce code est
        // soit un renouvellement, soit une amélioration — jamais une perte.
        $carryOverFrom = (clone $others)->max('ends_at');

        $others->update(['status' => 'cancelled']);

        $subscription->renew($carryOverFrom ? \Carbon\Carbon::parse($carryOverFrom) : null);
        $subscription->user->notify(new SubscriptionActivated($subscription));
    }

    // Commande issue d'une proposition ou d'une offre personnalisée : c'est seulement maintenant
    // que le paiement est confirmé qu'on accepte la proposition (ce qui ferme la demande et
    // refuse les autres) ou l'offre.
    private function finalizeNegotiatedOrder(Order $order): void
    {
        $proposal = $order->proposal;

        if ($proposal && $proposal->status === 'pending') {
            $rivals = Proposal::where('service_request_id', $proposal->service_request_id)
                ->where('id', '!=', $proposal->id)
                ->where('status', 'pending')
                ->with('prestataire')
                ->get();

            $proposal->accept();

            foreach ($rivals as $rival) {
                $rival->prestataire->notify(new ProposalRejected($rival->fresh()));
            }
        }

        $offer = $order->customOffer;

        // 'expired' inclus (audit externe) : le paiement a pu se confirmer juste après que la
        // commande planifiée ExpireStaleOffers ait marqué l'offre expirée entre-temps — la
        // commande est activée quand même (l'argent est réel), donc l'offre doit refléter
        // qu'elle a bien été honorée, pas rester bloquée sur "expirée" alors que la commande
        // tourne normalement.
        if ($offer && in_array($offer->status, ['pending', 'expired'], true)) {
            $offer->update(['status' => 'accepted']);
        }
    }

    // Marquer comme échoué
    // Confirme qu'un remboursement en attente a bien été traité manuellement — indépendant de
    // Order::confirmRefund() : corrige un point signalé par un 2e audit externe où un paiement
    // "orphelin" (dont la commande elle-même n'est pas/plus en payment_status='refund_pending' —
    // ex. commande déjà annulée autrement, ou paiement d'abonnement sans commande) n'avait aucune
    // action possible dans l'admin et restait indéfiniment dans "Remboursements à traiter".
    public function confirmRefund(): void
    {
        $this->update(['status' => 'refunded']);
    }

    public function markAsFailed(?string $gatewayResponse = null)
    {
        $this->status = 'failed';
        if ($gatewayResponse !== null) {
            $this->gateway_response = $gatewayResponse;
        }
        $this->save();

        // Un abonnement encore 'pending' rattaché à ce paiement n'a plus aucune chance d'être
        // activé : sans ça, un crédit de parrainage éventuellement consommé dessus restait perdu
        // pour toujours (audit externe — voir Subscription::cancelAbandoned()).
        if ($this->subscription && $this->subscription->status === 'pending') {
            $this->subscription->cancelAbandoned();
        }
    }
}
