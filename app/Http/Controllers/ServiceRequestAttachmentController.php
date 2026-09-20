<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceRequestAttachmentController extends Controller
{
    /**
     * Télécharger une pièce jointe d'une demande de service : réservé au client
     * auteur, aux prestataires (public cible des opportunités) et aux admins.
     */
    public function __invoke(ServiceRequest $serviceRequest, int $index)
    {
        $user = Auth::user();

        abort_unless(
            $serviceRequest->client_id === $user->id || $user->isPrestataire() || $user->role === 'admin',
            403
        );

        $file = ($serviceRequest->attachments ?? [])[$index] ?? null;

        abort_if($file === null || !Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name']);
    }
}
