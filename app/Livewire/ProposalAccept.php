<?php

namespace App\Livewire;

use App\Exceptions\OrderNoLongerPayableException;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\ProposalRejected;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProposalAccept extends Component
{
    public ServiceRequest $serviceRequest;
    public Proposal $proposal;
    public string $paymentMethod = 'mtn_momo';
    public bool $useReferralCredit = false;
    public string $promoCode = '';

    public function mount(ServiceRequest $serviceRequest, Proposal $proposal)
    {
        abort_unless($serviceRequest->client_id === Auth::id(), 403);
        abort_unless($proposal->service_request_id === $serviceRequest->id, 404);
        abort_unless($proposal->status === 'pending', 403, 'Cette proposition ne peut plus être acceptée.');
        abort_unless($serviceRequest->status === 'open', 403, 'Cette demande n\'est plus ouverte.');

        $this->serviceRequest = $serviceRequest->load('category');
        $this->proposal = $proposal->load('prestataire');

        // Un prestataire désactivé ou supprimé après avoir envoyé cette proposition ne doit plus
        // pouvoir être payé (audit externe — 8e audit) : contrairement à une commande directe sur
        // un service (Service::isOrderable()), ce parcours négocié n'appliquait aucune
        // vérification équivalente — l'argent partait en escrow vers un compte qui ne livrerait
        // jamais.
        abort_if(
            !$this->proposal->prestataire?->canReceiveOrders(),
            403,
            'Ce prestataire n\'est plus disponible pour accepter de nouvelles commandes.'
        );
    }

    public function confirm(PaymentService $payments)
    {
        $this->validate([
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        // La proposition et la demande sont revérifiées à l'intérieur même de la
        // transaction (verrouillées), pas seulement au chargement de la page.
        // IMPORTANT : rien n'est « accepté » ni « fermé » ici. La proposition n'est acceptée
        // (et la demande fermée, les autres propositions refusées) qu'une fois le paiement
        // confirmé (Payment::markAsPaid) — sinon abandonner la page de paiement laissait la
        // demande fermée pour toujours.
        $order = DB::transaction(function () {
            $proposal = Proposal::whereKey($this->proposal->id)->lockForUpdate()->first();
            $serviceRequest = ServiceRequest::whereKey($this->serviceRequest->id)->lockForUpdate()->first();

            if (!$proposal || $proposal->status !== 'pending' || !$serviceRequest || $serviceRequest->status !== 'open') {
                abort(403, 'Cette proposition ne peut plus être acceptée.');
            }

            // Revérifié sous verrou (audit externe — 8e audit) : le contrôle de mount() peut avoir
            // été fait avant qu'un admin ne désactive/supprime ce prestataire.
            $prestataire = User::withTrashed()->whereKey($proposal->user_id)->first();

            if (!$prestataire || !$prestataire->canReceiveOrders()) {
                abort(403, 'Ce prestataire n\'est plus disponible pour accepter de nouvelles commandes.');
            }

            // Déjà une commande en attente de paiement pour cette proposition : on la reprend.
            $existing = Order::where('proposal_id', $proposal->id)->where('status', 'pending_payment')->first();
            if ($existing) {
                return $existing;
            }

            // Une commande impayée d'une autre proposition de la même demande est abandonnée.
            // refund() restitue au passage un éventuel crédit de parrainage ou code promo déjà
            // consommé sur cette tentative — sinon il disparaîtrait sans avoir payé quoi que ce soit.
            Order::where('service_request_id', $serviceRequest->id)
                ->where('status', 'pending_payment')
                ->get()
                ->each(fn (Order $o) => $o->refund('Une autre proposition a été choisie pour cette demande.'));

            $amount = (float) $proposal->proposed_price;
            $commission = round($amount * $prestataire->commissionRate(), 2);
            $clientFee = round($amount * Order::CLIENT_FEE_RATE, 2);

            return Order::create([
                'client_id' => Auth::id(),
                'prestataire_id' => $proposal->user_id,
                'service_request_id' => $serviceRequest->id,
                'proposal_id' => $proposal->id,
                'requirements' => $serviceRequest->description,
                'amount' => $amount,
                'commission' => $commission,
                'client_fee' => $clientFee,
                'prestataire_amount' => $amount - $commission,
                'delivery_time' => $proposal->delivery_time,
                // Une proposition répond à une demande ouverte du client, sans service existant
                // à qui emprunter un nombre de révisions : on retombe sur le défaut de la plateforme.
                'revisions_included' => Order::DEFAULT_REVISIONS_INCLUDED,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);
        });

        if ($promoError = $order->applyPromoCode($this->promoCode)) {
            $this->addError('promoCode', $promoError);

            return;
        }

        try {
            $url = $payments->initiateForOrder($order, $this->paymentMethod, $this->useReferralCredit);
        } catch (OrderNoLongerPayableException $e) {
            return redirect()->route('orders.show', $order)
                ->with('error', "Cette commande n'est plus disponible pour le paiement (annulée ou expirée entre-temps). Consultez son statut actuel ci-dessous.");
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('orders.show', $order)
                ->with('error', 'La commande a été créée mais le paiement n\'a pas pu être initié. Réessayez depuis la page de la commande.');
        }

        return redirect()->away($url);
    }

    public function render()
    {
        return view('livewire.proposal-accept')
            ->layout('components.layouts.app');
    }
}
