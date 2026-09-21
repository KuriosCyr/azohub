<?php

namespace App\Models;

use App\Notifications\PaymentConfirmed;
use App\Notifications\ProposalRejected;
use App\Notifications\SubscriptionActivated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status',
        'type',
        'gateway_response',
        'gateway_reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
        $this->status = 'success';
        $this->paid_at = now();
        if ($gatewayResponse !== null) {
            $this->gateway_response = $gatewayResponse;
        }
        $this->save();

        // La commande passe en "paid" et le paiement reste bloqué en escrow
        // jusqu'à validation de la livraison par le client (cf. Order::releasePayment()).
        if ($this->order && $this->order->status === 'pending_payment') {
            $this->order->update([
                'status' => 'paid',
                'payment_status' => 'held',
            ]);

            $this->order->prestataire->notify(new PaymentConfirmed($this->order));

            $this->finalizeNegotiatedOrder($this->order);
        }

        // Un seul abonnement actif à la fois : celui-ci remplace tout abonnement en cours.
        if ($this->subscription) {
            $others = Subscription::where('user_id', $this->subscription->user_id)
                ->where('id', '!=', $this->subscription->id)
                ->where('status', 'active');

            // Renouvellement anticipé du même plan : on conserve le temps restant.
            $carryOverFrom = (clone $others)
                ->where('subscription_plan_id', $this->subscription->subscription_plan_id)
                ->max('ends_at');

            $others->update(['status' => 'cancelled']);

            $this->subscription->renew($carryOverFrom ? \Carbon\Carbon::parse($carryOverFrom) : null);
            $this->subscription->user->notify(new SubscriptionActivated($this->subscription));
        }
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

        if ($offer && $offer->status === 'pending') {
            $offer->update(['status' => 'accepted']);
        }
    }

    // Marquer comme échoué
    public function markAsFailed(?string $gatewayResponse = null)
    {
        $this->status = 'failed';
        if ($gatewayResponse !== null) {
            $this->gateway_response = $gatewayResponse;
        }
        $this->save();
    }
}
