<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Afficher le formulaire de contact
     */
    public function index()
    {
        return view('pages.contact');
    }

    /**
     * Traiter l'envoi du formulaire
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'subject.required' => 'Le sujet est obligatoire.',
            'message.required' => 'Le message est obligatoire.',
        ]);

        try {
            Mail::to(config('services.azohub.support_email'))->send(new ContactMessage($validated));
        } catch (\Throwable $e) {
            Log::error('Échec envoi du formulaire de contact : ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Votre message n\'a pas pu être envoyé. Réessayez dans quelques instants.');
        }

        return redirect()
            ->back()
            ->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
    }
}