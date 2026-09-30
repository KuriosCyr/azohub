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
use Illuminate\Support\Facades\DB;
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
     * Statut courant de la commande, en JSON. Sondé côté client (voir orders/show.blade.php)
     * pour rafraîchir la page dès que le webhook FedaPay confirme un paiement, sans que le
     * client n'ait besoin de recharger manuellement la page.
     */
    public function status(Order $order)
    {
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        return response()->json(['status' => $order->status]);
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

        // Vérifier le statut — reverrouillé dans la transaction ci-dessous contre une annulation
        // client simultanée (ex. cancel() sur l'autre onglet du client au même instant).
        if ($order->status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut pas être acceptée.');
        }

        $accepted = DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (!$locked || $locked->status !== 'paid') {
                return false;
            }

            // Pour une commande négociée, le compte à rebours de livraison n'a pas encore
            // démarré (cf. Payment::markAsPaid()) : il démarre maintenant, au moment où le
            // prestataire accepte formellement le travail.
            $locked->update([
                'status' => 'in_progress',
                'accepted_at' => now(),
                'expected_delivery_at' => ($locked->isNegotiated() && $locked->delivery_time)
                    ? now()->addDays($locked->delivery_time)
                    : $locked->expected_delivery_at,
            ]);

            return true;
        });

        if (!$accepted) {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut plus être acceptée (elle a peut-être été annulée entre-temps).');
        }

        $order->refresh();

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

        // Vérifier le statut — reverrouillé dans la transaction ci-dessous contre une action
        // simultanée sur la même commande (ex. le client valide au même instant).
        if ($order->status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut pas être refusée.');
        }

        $validated = $request->validate([
            'refusal_reason' => 'nullable|string|max:500',
        ]);

        $refused = DB::transaction(function () use ($order, $validated) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (!$locked || $locked->status !== 'paid') {
                return false;
            }

            $locked->refund($validated['refusal_reason'] ?? 'Refusé par le prestataire');

            return true;
        });

        if (!$refused) {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut plus être refusée.');
        }

        $order->refresh();
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
                $path = $file->store('deliverables/' . $order->id, 'local');
                $deliverables[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Verrouillée : un double-clic sur "Livrer" (ou deux onglets) ne doit pas fusionner deux
        // fois le même lot de fichiers dans "deliverables", ni réinitialiser deux fois le délai
        // de validation de 72h.
        $delivered = DB::transaction(function () use ($order, $validated, $deliverables) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (!$locked || $locked->status !== 'in_progress') {
                return false;
            }

            $locked->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                // Renseigné une seule fois : sert de référence pour juger la ponctualité, une
                // re-livraison après révision ne doit pas la faire passer "en retard" après coup.
                'first_delivered_at' => $locked->first_delivered_at ?? now(),
                'delivery_note' => $validated['delivery_notes'] ?? null,
                // Re-livraison après une révision sans nouveau fichier : on garde les fichiers déjà livrés.
                'deliverables' => !empty($deliverables) ? array_merge($locked->deliverables ?? [], $deliverables) : ($locked->deliverables ?: null),
                'validation_deadline' => now()->addHours(72),
                // Sans ce reset, une commande sur laquelle une révision a un jour été demandée
                // continuait à apparaître dans le "À faire" du dashboard prestataire indéfiniment —
                // y compris après cette re-livraison, sa validation par le client, voire une fois
                // la commande "completed" : ce champ n'était jamais remis à false nulle part.
                'revision_requested' => false,
            ]);

            return true;
        });

        if (!$delivered) {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut plus être marquée comme livrée.');
        }

        $order->refresh();

        // Recalculé dès la livraison (pas seulement à la validation finale par le client, qui
        // peut arriver des jours plus tard) : first_delivered_at vient d'être fixé, la
        // ponctualité de cette commande est donc déjà connue.
        $order->prestataire->updatePunctuality();

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

        // Libérer le paiement au prestataire et clôturer la commande. Ne notifie plus sans
        // condition (audit externe — 2e audit) : un litige ouvert entre ce contrôle de statut et
        // le verrou posé dans releasePayment() pouvait faire échouer la libération en silence,
        // tout en envoyant quand même "le prestataire a reçu son paiement" — un message trompeur.
        $released = $order->releasePayment();

        if (!$released) {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut plus être validée (un litige a peut-être été ouvert entre-temps).');
        }

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

        // Quota de révisions incluses (fixé sur la commande à sa création) : au-delà, un
        // client de mauvaise foi pourrait bloquer indéfiniment le paiement du prestataire en
        // enchaînant les demandes. Le litige reste l'issue si le travail n'est vraiment pas
        // conforme une fois le quota épuisé.
        if (!$order->canRequestRevision()) {
            return redirect()
                ->back()
                ->with('error', "Vous avez déjà utilisé les {$order->revisions_included} révision(s) incluse(s) pour cette commande. Si le travail livré n'est vraiment pas conforme, ouvrez un litige.");
        }

        $validated = $request->validate([
            'revision_notes' => 'required|string|max:1000',
        ]);

        // Verrouillée : un double-clic ne doit pas consommer deux révisions du quota pour une
        // seule vraie demande.
        $requested = DB::transaction(function () use ($order, $validated) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (!$locked || !$locked->canRequestRevision()) {
                return false;
            }

            $locked->update([
                'status' => 'in_progress',
                'revision_requested' => true,
                'revision_notes' => $validated['revision_notes'],
                'revisions_used' => $locked->revisions_used + 1,
            ]);

            return true;
        });

        if (!$requested) {
            return redirect()
                ->back()
                ->with('error', "Vous avez déjà utilisé les {$order->revisions_included} révision(s) incluse(s) pour cette commande. Si le travail livré n'est vraiment pas conforme, ouvrez un litige.");
        }

        $order->refresh();
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

        // Verrouillée : sans ça, une annulation client et une acceptation prestataire
        // simultanées pourraient toutes deux passer leur vérification de statut avant que
        // l'autre n'écrive, laissant la commande dans un état contradictoire.
        $cancelled = DB::transaction(function () use ($order, $validated) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (!$locked || !in_array($locked->status, ['pending_payment', 'paid'])) {
                return false;
            }

            $locked->refund($validated['cancellation_reason']);

            return true;
        });

        if (!$cancelled) {
            return redirect()
                ->back()
                ->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $order->refresh();

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
        
        if (!Storage::disk('local')->exists($file['path'])) {
            abort(404);
        }

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}