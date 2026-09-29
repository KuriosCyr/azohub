<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    // Reçu PDF d'une commande : le contenu diffère selon qui le télécharge (le client voit ce
    // qu'il a payé, le prestataire voit sa commission et son net) — jamais de "facture" au sens
    // fiscal strict (numéro RCCM/IFU non disponible), volontairement présenté comme un reçu.
    public function download(Order $order)
    {
        $isClient = $order->client_id === Auth::id();
        $isPrestataire = $order->prestataire_id === Auth::id();

        abort_unless($isClient || $isPrestataire, 403);

        // Un reçu n'a de sens qu'une fois un paiement réellement effectué — avant ça
        // (pending_payment), il n'y a rien à justifier.
        abort_if($order->payment_status === 'pending', 404);

        $order->load(['client', 'prestataire', 'service', 'payments' => function ($query) {
            $query->where('status', '!=', 'pending')->latest();
        }]);

        $payment = $order->payments->first();

        $pdf = Pdf::loadView('pdf.order-receipt', [
            'order' => $order,
            'payment' => $payment,
            'forClient' => $isClient,
        ])->setPaper('a4', 'portrait');

        $filename = ($isClient ? 'recu' : 'facture') . '-' . $order->order_number . '.pdf';

        return $pdf->download($filename);
    }
}
