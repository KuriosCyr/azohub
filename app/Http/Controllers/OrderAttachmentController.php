<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderAttachmentController extends Controller
{
    /**
     * Télécharger (ou prévisualiser, pour une image) une pièce jointe fournie par le client
     * à la commande : réservé au client, au prestataire concernés, et aux admins.
     */
    public function __invoke(Order $order, int $index)
    {
        $user = Auth::user();

        abort_unless(
            $order->client_id === $user->id || $order->prestataire_id === $user->id || $user->role === 'admin',
            403
        );

        $file = ($order->attachments ?? [])[$index] ?? null;

        abort_if($file === null || !Storage::disk('local')->exists($file['path']), 404);

        // Même logique que les pièces jointes du chat (ConversationController/MessageController) :
        // une image s'affiche directement dans le navigateur, tout le reste force le téléchargement.
        $isImage = in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        return $isImage
            ? Storage::disk('local')->response($file['path'])
            : Storage::disk('local')->download($file['path'], $file['name']);
    }
}
