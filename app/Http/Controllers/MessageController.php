<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Store a new message.
     */
    public function store(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur fait partie de la commande
        if ($order->client_id !== Auth::id() && $order->prestataire_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'attachments.*' => 'nullable|file|max:10240', // 10MB
        ]);

        // Déterminer le destinataire
        $receiverId = $order->client_id === Auth::id() 
            ? $order->prestataire_id 
            : $order->client_id;

        // Upload des pièces jointes
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('messages/' . $order->id, 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Créer le message
        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $validated['message'],
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        // TODO: Envoyer notification au destinataire

        return redirect()
            ->back()
            ->with('success', 'Message envoyé avec succès !');
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Order $order)
    {
        // Marquer tous les messages reçus comme lus
        Message::where('order_id', $order->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

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
        
        if (!Storage::disk('public')->exists($file['path'])) {
            abort(404);
        }

        return Storage::disk('public')->download($file['path'], $file['name']);
    }
}