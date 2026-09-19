<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\OrderAccepted;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderRefused;
use App\Notifications\PaymentReleased;
use App\Notifications\RevisionRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Vérifier que l'utilisateur est autorisé à voir cette commande
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir cette commande.');
        }

        // Charger les relations
        $order->load([
            'client',
            'prestataire',
            'service.category',
            'review',
            'payments',
            'dispute',
            'statusHistory.updatedBy',
        ]);

        // Déterminer le rôle de l'utilisateur pour cette commande
        $userRole = $order->client_id === Auth::id() ? 'client' : 'prestataire';

        return view('orders.show', compact('order', 'userRole'));
    }

    /**
     * Prestataire accepte la commande.
     */
    public function accept(Order $order)
    {
        // Vérifier que c'est le bon prestataire
        if ($order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier le statut
        if ($order->status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut pas être acceptée.');
        }

        // Mettre à jour le statut
        $order->update([
            'status' => 'in_progress',
            'accepted_at' => now(),
        ]);

        $order->client->notify(new OrderAccepted($order));

        return redirect()
            ->back()
            ->with('success', 'Commande acceptée ! Vous pouvez maintenant commencer le travail.');
    }

    /**
     * Prestataire refuse la commande.
     */
    public function refuse(Order $order, Request $request)
    {
        // Vérifier que c'est le bon prestataire
        if ($order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier le statut
        if ($order->status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut pas être refusée.');
        }

        $validated = $request->validate([
            'refusal_reason' => 'nullable|string|max:500',
        ]);

        $order->refund($validated['refusal_reason'] ?? 'Refusé par le prestataire');

        $order->client->notify(new OrderRefused($order));

        return redirect()
            ->route('prestataire.dashboard')
            ->with('success', 'Commande refusée. Le remboursement du client est en cours de traitement.');
    }

    /**
     * Prestataire marque la commande comme livrée.
     */
    public function markAsDelivered(Order $order, Request $request)
    {
        // Vérifier que c'est le bon prestataire
        if ($order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier le statut
        if ($order->status !== 'in_progress') {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut pas être marquée comme livrée.');
        }

        $validated = $request->validate([
            'delivery_notes' => 'nullable|string|max:1000',
            'deliverables.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar,mp4,mp3,mov', // 10MB max
        ]);

        // Upload des livrables
        $deliverables = [];
        if ($request->hasFile('deliverables')) {
            foreach ($request->file('deliverables') as $file) {
                $path = $file->store('deliverables/' . $order->id, 'public');
                $deliverables[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Mettre à jour la commande
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_note' => $validated['delivery_notes'] ?? null,
            'deliverables' => !empty($deliverables) ? $deliverables : null,
        ]);

        $order->client->notify(new OrderDelivered($order));

        return redirect()
            ->back()
            ->with('success', 'Commande marquée comme livrée ! En attente de validation du client.');
    }

    /**
     * Client valide la livraison.
     */
    public function validate(Order $order)
    {
        // Vérifier que c'est le bon client
        if ($order->client_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier le statut
        if ($order->status !== 'delivered') {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut pas être validée.');
        }

        // Libérer le paiement au prestataire et clôturer la commande
        $order->releasePayment();

        $order->prestataire->notify(new PaymentReleased($order));

        return redirect()
            ->back()
            ->with('success', 'Commande validée ! Le prestataire a reçu son paiement.');
    }

    /**
     * Demander une révision.
     */
    public function requestRevision(Order $order, Request $request)
    {
        // Vérifier que c'est le bon client
        if ($order->client_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier le statut
        if ($order->status !== 'delivered') {
            return redirect()
                ->back()
                ->with('error', 'Vous ne pouvez pas demander de révision pour cette commande.');
        }

        $validated = $request->validate([
            'revision_notes' => 'required|string|max:1000',
        ]);

        // Remettre en cours
        $order->update([
            'status' => 'in_progress',
            'revision_requested' => true,
            'revision_notes' => $validated['revision_notes'],
        ]);

        $order->prestataire->notify(new RevisionRequested($order));

        return redirect()
            ->back()
            ->with('success', 'Demande de révision envoyée au prestataire.');
    }

    /**
     * Annuler la commande.
     */
    public function cancel(Order $order, Request $request)
    {
        // Vérifier l'autorisation
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que la commande peut être annulée
        if (!in_array($order->status, ['pending_payment', 'paid'])) {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $order->refund($validated['cancellation_reason']);

        $cancelledByRole = $order->client_id === Auth::id() ? 'client' : 'prestataire';
        $recipient = $cancelledByRole === 'client' ? $order->prestataire : $order->client;
        $recipient->notify(new OrderCancelled($order, $cancelledByRole));

        $message = $order->payment_status === 'refund_pending'
            ? 'Commande annulée. Le remboursement du client est en cours de traitement.'
            : 'Commande annulée avec succès.';

        return redirect()
            ->route('dashboard')
            ->with('success', $message);
    }

    /**
     * Download deliverable file.
     */
    public function downloadDeliverable(Order $order, $index)
    {
        // Vérifier l'autorisation
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        $deliverables = $order->deliverables ?? [];

        if (!isset($deliverables[$index])) {
            abort(404);
        }

        $file = $deliverables[$index];
        
        if (!Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        return Storage::disk('public')->download($file['path'], $file['name']);
    }
}