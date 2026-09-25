<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    /**
     * Trouver ou créer la conversation directe entre le client connecté et un prestataire.
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'prestataire_id' => 'required|exists:users,id',
            'service_id' => 'nullable|integer',
        ]);

        $prestataire = User::where('role', 'prestataire')->findOrFail($validated['prestataire_id']);

        // Le service doit appartenir à ce prestataire, sinon il pourrait être associé
        // à la conversation (et recopié dans les offres/commandes) par un tiers.
        if (!empty($validated['service_id'])
            && !Service::where('id', $validated['service_id'])->where('user_id', $prestataire->id)->exists()) {
            $validated['service_id'] = null;
        }

        $conversation = Conversation::firstOrCreate(
            ['client_id' => Auth::id(), 'prestataire_id' => $prestataire->id],
            ['service_id' => $validated['service_id'] ?? null]
        );

        if (!empty($validated['service_id']) && !$conversation->service_id) {
            $conversation->update(['service_id' => $validated['service_id']]);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Télécharger une pièce jointe d'un message de conversation directe.
     */
    public function downloadAttachment(ConversationMessage $message, int $index)
    {
        if (!$message->conversation->hasParticipant(Auth::id())) {
            abort(403);
        }

        $attachments = $message->attachments;

        if (!isset($attachments[$index])) {
            abort(404);
        }

        $file = $attachments[$index];

        if (!Storage::disk('local')->exists($file['path'])) {
            abort(404);
        }

        // Une image cliquée dans la conversation doit s'afficher (aperçu), pas se télécharger —
        // ::download() force systématiquement Content-Disposition: attachment, y compris pour
        // les images, d'où le téléchargement au lieu de l'aperçu attendu. Les autres types de
        // fichiers (PDF, documents...) continuent de se télécharger, c'est le comportement voulu.
        if (in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return Storage::disk('local')->response($file['path']);
        }

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}
