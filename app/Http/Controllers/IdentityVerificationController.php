<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentityVerificationController extends Controller
{
    /**
     * Soumettre une pièce d'identité pour vérification.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isPrestataire()) {
            abort(403);
        }

        if ($user->identity_verification_status === 'pending' || $user->identity_verification_status === 'verified') {
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Une vérification est déjà en cours ou validée pour votre compte.');
        }

        $request->validate([
            'identity_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'identity_document.required' => 'Veuillez sélectionner un fichier.',
            'identity_document.mimes' => 'Le document doit être une image (JPG, PNG) ou un PDF.',
            'identity_document.max' => 'Le fichier ne doit pas dépasser 5 MB.',
        ]);

        // Disque 'local' (privé, non lié au symlink public/storage) : une pièce
        // d'identité ne doit être consultable que par son propriétaire et les
        // administrateurs, jamais accessible par URL directe.
        $path = $request->file('identity_document')->store('identity-documents', 'local');

        $user->submitIdentityDocument($path);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Votre document a été soumis. Notre équipe l\'examinera sous peu.');
    }

    /**
     * Afficher/télécharger le document — uniquement pour son propriétaire ou un admin.
     */
    public function show(\App\Models\User $user)
    {
        if (Auth::id() !== $user->id && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!$user->identity_document || !\Illuminate\Support\Facades\Storage::disk('local')->exists($user->identity_document)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->response($user->identity_document);
    }
}
