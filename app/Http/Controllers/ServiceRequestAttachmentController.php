<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceRequestAttachmentController extends Controller
{
    /**
     * Télécharger une pièce jointe d'une demande de service : réservé au client
     * auteur, aux admins, et — tant que la demande est ouverte — à tout prestataire
     * (public cible des opportunités). Une fois une proposition acceptée, seul le
     * prestataire retenu garde l'accès (audit externe — 4e audit) : les autres n'ont
     * plus aucune raison légitime de continuer à télécharger les pièces jointes d'une
     * demande qui ne les concerne plus.
     */
    public function __invoke(ServiceRequest $serviceRequest, int $index)
    {
        $user = Auth::user();

        $isOpenToAnyPrestataire = $serviceRequest->status === 'open' && $user->isPrestataire();
        $isRetainedPrestataire = $user->isPrestataire()
            && $serviceRequest->proposals()->where('status', 'accepted')->where('user_id', $user->id)->exists();

        abort_unless(
            $serviceRequest->client_id === $user->id
                || $user->role === 'admin'
                || $isOpenToAnyPrestataire
                || $isRetainedPrestataire,
            403
        );

        $file = ($serviceRequest->attachments ?? [])[$index] ?? null;

        abort_if($file === null || !Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}
