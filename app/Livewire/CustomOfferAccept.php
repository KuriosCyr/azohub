<?php

namespace App\Livewire;

use App\Models\CustomOffer;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CustomOfferAccept extends Component
{
    public CustomOffer $offer;
    public string $paymentMethod = 'mtn_momo';

    public function mount(CustomOffer $offer)
    {
        abort_unless($offer->client_id === Auth::id(), 403);
        abort_unless($offer->status === 'pending', 403, 'Cette offre ne peut plus être acceptée.');

        $this->offer = $offer->load('prestataire', 'service');
    }

    public function confirm(PaymentService $payments)
    {
        $this->validate([
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        // L'offre est revérifiée à l'intérieur même de la transaction (verrouillée),
        // pas seulement au chargement de la page : sans ça, une offre déjà acceptée
        // ou déclinée entre-temps pourrait quand même générer une seconde commande.
        $order = DB::transaction(function () {
            $offer = CustomOffer::whereKey($this->offer->id)->lockForUpdate()->first();

            if (!$offer || $offer->status !== 'pending') {
                abort(403, 'Cette offre ne peut plus être acceptée.');
            }

            $amount = (float) $offer->price;
            $commission = round($amount * $offer->prestataire->commissionRate(), 2);
            $clientFee = round($amount * Order::CLIENT_FEE_RATE, 2);

            $order = Order::create([
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
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);

            $offer->update(['status' => 'accepted']);

            return $order;
        });

        try {
            $url = $payments->initiateForOrder($order, $this->paymentMethod);
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
