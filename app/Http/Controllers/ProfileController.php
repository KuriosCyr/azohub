<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Order;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Validation personnalisée selon le rôle
        $validated = $request->validated();

        // Upload avatar si présent
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Sauvegarder le nouveau
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        // Mettre à jour les informations
        $user->fill($validated);

        // Si l'email a changé, réinitialiser la vérification
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // 'min:8' seul ici (déjà corrigé ci-dessus) + validate() au lieu de validateWithBag() :
        // la vue affiche les erreurs via $errors->updatePassword, un sac qui n'était jamais
        // rempli — les erreurs de ce formulaire n'apparaissaient donc jamais à l'écran, même
        // si la validation les rejetait bien côté serveur.
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return Redirect::route('profile.edit')->with('success', 'Mot de passe modifié avec succès !');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $blockers = $this->accountDeletionBlockers($user);

        if (!empty($blockers)) {
            return Redirect::route('profile.edit')
                ->with('error', 'Suppression impossible pour le moment : ' . implode(' ', $blockers));
        }

        Auth::logout();

        $user->anonymizeAndDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Votre compte a été supprimé.');
    }

    // Raisons empêchant la suppression : un prestataire pourrait sinon disparaître avec un
    // solde non retiré, un retrait déjà en cours de traitement, ou une commande en cours
    // (client comme prestataire) — la commande resterait bloquée avec une partie devenue
    // "Utilisateur supprimé" et injoignable, sans que personne ne puisse la faire avancer.
    private function accountDeletionBlockers(User $user): array
    {
        $blockers = [];

        if ((float) $user->wallet_balance > 0) {
            $blockers[] = 'Vous avez un solde de ' . number_format((float) $user->wallet_balance, 0, ',', ' ') . ' FCFA non retiré : retirez-le d\'abord depuis votre portefeuille.';
        }

        if (WithdrawalRequest::where('prestataire_id', $user->id)->where('status', 'pending')->exists()) {
            $blockers[] = 'Une demande de retrait est en cours de traitement.';
        }

        $activeStatuses = ['pending_payment', 'paid', 'in_progress', 'delivered', 'disputed'];

        $hasActiveOrder = Order::whereIn('status', $activeStatuses)
            ->where(fn ($query) => $query->where('client_id', $user->id)->orWhere('prestataire_id', $user->id))
            ->exists();

        if ($hasActiveOrder) {
            $blockers[] = 'Vous avez au moins une commande en cours : attendez qu\'elle soit finalisée, validée ou annulée.';
        }

        return $blockers;
    }
}