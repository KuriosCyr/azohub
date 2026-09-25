<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Download attachment.
     */
    public function downloadAttachment(Message $message, $index)
    {
        // Vérifier l'autorisation
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
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

        // Voir ConversationController::downloadAttachment() pour la même correction : une image
        // doit s'afficher (aperçu), pas se télécharger.
        if (in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return Storage::disk('local')->response($file['path']);
        }

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}