<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
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
            'service_id' => 'nullable|exists:services,id',
        ]);

        $prestataire = User::where('role', 'prestataire')->findOrFail($validated['prestataire_id']);

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

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}
