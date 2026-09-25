<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // La règle 'email' seule accepte un domaine sans point ("user@yopmail" est
            // syntaxiquement valide) — la regex exige un vrai domaine avec extension.
            // ('email:rfc,dns' vérifierait aussi que le domaine existe réellement, mais
            // ajouterait une dépendance réseau à chaque inscription — trop fragile.)
            'email' => ['required', 'string', 'lowercase', 'email', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/', 'max:255', 'unique:'.User::class],
            // Champ affiché comme obligatoire dans le formulaire mais jamais vérifié côté
            // serveur (ni la présence, ni le format) : n'importe quoi passait, y compris un
            // email tapé par erreur dans ce champ.
            // Autorise les espaces/tirets (le champ suggère "+229 XX XX XX XX"), rejette
            // tout ce qui contient des lettres ou un @ — exactement ce qui posait problème.
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s.-]{8,20}$/'],
            'city' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:client,prestataire'],
        ], [
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.regex' => 'Le numéro de téléphone n\'est pas valide.',
        ]);

        // Le formulaire envoie un champ radio "role" (client/prestataire), pas
        // "is_prestataire" : ce nom ne correspondait à rien dans la requête, donc ce
        // test était toujours faux et tout le monde était inscrit comme client.
        $role = $request->role === 'prestataire' ? 'prestataire' : 'client';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'city' => $request->city,
            'role' => $role,
            'password' => Hash::make($request->password),
        ]);

        // En local, aucun service d'envoi d'email réel n'est configuré (MAIL_MAILER=log) :
        // un compte de test resterait bloqué sur "confirmez votre email" sans jamais recevoir
        // le lien. Ne s'applique jamais hors environnement local.
        if (app()->environment(['local', 'testing'])) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        event(new Registered($user));

        Auth::login($user);

        // Redirige vers le dashboard approprié selon le rôle
        if ($role === 'prestataire') {
            return redirect()->route('dashboard')->with('success', 'Bienvenue sur Azohub ! Vous pouvez maintenant créer vos premiers services.');
        }

        return redirect()->route('dashboard')->with('success', 'Bienvenue sur Azohub !');
    }
}
