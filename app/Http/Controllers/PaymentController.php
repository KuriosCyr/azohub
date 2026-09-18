<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Démarre (ou relance) le paiement Mobile Money d'une commande et redirige
     * le client vers la page de paiement hébergée par FedaPay.
     */
    public function initiate(Order $order, Request $request, PaymentService $payments)
    {
        if ($order->client_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->isPendingPayment()) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Cette commande n\'est plus en attente de paiement.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        $url = $payments->initiateForOrder($order, $validated['payment_method']);

        return redirect()->away($url);
    }

    /**
     * Retour navigateur après paiement (informatif). La confirmation réelle
     * du paiement se fait de façon asynchrone via le webhook.
     */
    public function callback(Order $order)
    {
        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Paiement en cours de confirmation. Le statut sera mis à jour sous quelques instants.');
    }

    /**
     * Retour navigateur après paiement d'un abonnement (informatif). La confirmation
     * réelle se fait de façon asynchrone via le webhook.
     */
    public function subscriptionCallback(Subscription $subscription)
    {
        return redirect()
            ->route('prestataire.subscription')
            ->with('success', 'Paiement en cours de confirmation. Votre abonnement sera activé sous quelques instants.');
    }

    /**
     * Notification serveur-à-serveur envoyée par FedaPay.
     */
    public function webhook(Request $request, PaymentService $payments)
    {
        try {
            $payments->handleWebhook(
                $request->getContent(),
                (string) $request->header('X-FEDAPAY-SIGNATURE')
            );
        } catch (\Throwable $e) {
            Log::warning('Webhook FedaPay rejeté : ' . $e->getMessage());

            return response()->json(['error' => 'invalid'], 400);
        }

        return response()->json(['status' => 'ok']);
    }
}
