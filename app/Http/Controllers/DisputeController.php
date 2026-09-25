<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Order;
use App\Notifications\DisputeOpened;
use App\Services\AdminNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DisputeController extends Controller
{
    /**
     * Ouvrir un litige sur une commande.
     */
    public function store(Order $order, Request $request)
    {
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->canOpenDispute()) {
            return redirect()
                ->back()
                ->with('error', 'Un litige ne peut pas être ouvert sur cette commande.');
        }

        $validated = $request->validate([
            'reason' => 'required|in:work_not_delivered,work_not_conform,poor_quality,late_delivery,payment_issue,other',
            'description' => 'required|string|min:20|max:2000',
            'evidences' => 'nullable|array|max:5',
            'evidences.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'reason.required' => 'Veuillez sélectionner une raison.',
            'description.required' => 'Veuillez décrire le problème.',
            'description.min' => 'La description doit faire au moins 20 caractères.',
            'evidences.max' => 'Vous pouvez joindre 5 fichiers maximum.',
            'evidences.*.mimes' => 'Chaque preuve doit être une image (JPG, PNG) ou un PDF.',
            'evidences.*.max' => 'Chaque fichier ne doit pas dépasser 5 MB.',
        ]);

        $evidences = [];
        if ($request->hasFile('evidences')) {
            foreach ($request->file('evidences') as $file) {
                $path = $file->store('disputes/' . $order->id, 'local');
                $evidences[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $dispute = Dispute::create([
            'order_id' => $order->id,
            'opened_by' => Auth::id(),
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'evidences' => !empty($evidences) ? $evidences : null,
            'status' => 'open',
        ]);

        $order->update(['status' => 'disputed']);

        $otherParty = $order->client_id === Auth::id() ? $order->prestataire : $order->client;
        $otherParty->notify(new DisputeOpened($dispute));

        AdminNotifier::actionRequired(
            'Nouveau litige à examiner',
            "Un litige a été ouvert sur la commande {$order->order_number}.",
            route('filament.admin.resources.disputes.edit', $dispute),
        );

        return redirect()
            ->back()
            ->with('success', 'Votre litige a été enregistré. Notre équipe va examiner la situation sous peu.');
    }

    /**
     * Télécharger une preuve jointe à un litige (réservé aux admins, depuis le panel).
     */
    public function downloadEvidence(Dispute $dispute, int $index)
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $evidences = $dispute->evidences ?? [];

        if (!isset($evidences[$index])) {
            abort(404);
        }

        $file = $evidences[$index];

        if (!Storage::disk('local')->exists($file['path'])) {
            abort(404);
        }

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}
