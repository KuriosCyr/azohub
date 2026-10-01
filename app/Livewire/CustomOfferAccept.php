<?php

namespace App\Livewire;

use App\Exceptions\OrderNoLongerPayableException;
use App\Models\CustomOffer;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CustomOfferAccept extends Component
{
    public CustomOffer $offer;
    public string $paymentMethod = 'mtn_momo';
    public bool $useReferralCredit = false;
    public string $promoCode = '';

    public function mount(CustomOffer $offer)
    {
        abort_unless($offer->client_id === Auth::id(), 403);
        abort_unless($offer->status === 'pending', 403, 'Cette offre ne peut plus être acceptée.');
        abort_if($offer->isExpired(), 403, 'Cette offre a expiré.');

        $this->offer = $offer->load('prestataire', 'service');

        // Même garde-fou que ProposalAccept::mount() (audit externe — 8e audit) : un prestataire
        // désactivé ou supprimé après avoir envoyé cette offre ne doit plus pouvoir être payé.
        abort_if(
            !$this->offer->prestataire?->canReceiveOrders(),
            403,
            'Ce prestataire n\'est plus disponible pour accepter de nouvelles commandes.'
        );
    }

    public function confirm(PaymentService $payments)
    {
        $this->validate([
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        // L'offre est revérifiée à l'intérieur même de la transaction (verrouillée).
        // Elle n'est marquée « acceptée » qu'une fois le paiement confirmé
        // (Payment::markAsPaid) : abandonner le paiement ne la bloque plus.
        $order = DB::transaction(function () {
            $offer = CustomOffer::whereKey($this->offer->id)->lockForUpdate()->first();

            if (!$offer || $offer->status !== 'pending' || $offer->isExpired()) {
                abort(403, 'Cette offre ne peut plus être acceptée.');
            }

            // Revérifié sous verrou (audit externe — 8e audit) : le contrôle de mount() peut
            // avoir été fait avant qu'un admin ne désactive/supprime ce prestataire.
            $prestataire = User::withTrashed()->whereKey($offer->prestataire_id)->first();

            if (!$prestataire || !$prestataire->canReceiveOrders()) {
                abort(403, 'Ce prestataire n\'est plus disponible pour accepter de nouvelles commandes.');
            }

            $existing = Order::where('custom_offer_id', $offer->id)->where('status', 'pending_payment')->first();
            if ($existing) {
                return $existing;
            }

            $amount = (float) $offer->price;
            $commission = round($amount * $prestataire->commissionRate(), 2);
            $clientFee = round($amount * Order::CLIENT_FEE_RATE, 2);

            return Order::create([
                'client_id' => Auth::id(),
                'prestataire_id' => $offer->prestataire_id,
                'service_id' => $offer->service_id,
                'custom_offer_id' => $offer->id,
                'requirements' => $offer->description,
                'amount' => $amount,
                'commission' => $commission,
                'client_fee' => $clientFee,
                'prestataire_amount' => $amount - $commission,
                'delivery_time' => $offer->delivery_days,
                'revisions_included' => $offer->revisions_included ?? Order::DEFAULT_REVISIONS_INCLUDED,
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
        return view('livewire.custom-offer-accept')
            ->layout('components.layouts.app');
    }
}
