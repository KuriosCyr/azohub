<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Remplace DeleteAction + ForceDeleteAction (audit externe — 7e audit) :
            // - ForceDeleteAction permettait d'effacer DÉFINITIVEMENT un utilisateur, ce qui
            //   efface en cascade MySQL toutes ses commandes (y compris, pour un prestataire,
            //   l'historique de commandes du CLIENT avec qui il a travaillé), ses paiements, ses
            //   retraits, et les deux registres financiers (portefeuille, parrainage) — exactement
            //   le type de suppression déjà retiré pour les commandes, litiges et abonnements,
            //   mais ouvert ici par un chemin indirect.
            // - DeleteAction (soft delete Filament brut) contournait les garde-fous de
            //   ProfileController::destroy() (solde, retrait en attente, commande active) ET
            //   l'anonymisation RGPD (nom, e-mail, téléphone, pièce d'identité restaient intacts,
            //   juste masqués par deleted_at) — un prestataire supprimé ainsi avec une commande
            //   en cours faisait passer releasePayment() en 'released' sans créditer personne.
            Action::make('anonymize_and_delete')
                ->label('Supprimer')
                ->icon('heroicon-o-trash')
                ->color('danger')
                // !isAdmin() (audit externe — 8e audit) : rien n'empêchait de supprimer un
                // administrateur, y compris le dernier existant, ou de se supprimer soi-même —
                // ce qui bloquerait l'accès au panneau. Les comptes admin se gèrent à part
                // (créés/retirés directement en base par un autre admin si nécessaire).
                ->visible(fn (User $record) => !$record->trashed() && !$record->isAdmin())
                ->requiresConfirmation()
                ->modalHeading('Supprimer ce compte')
                ->modalDescription('Anonymise définitivement les données personnelles (nom, e-mail, téléphone, pièce d\'identité) puis supprime le compte. Impossible tant qu\'un solde, un retrait en cours ou une commande active existe.')
                ->action(function (User $record) {
                    if ($record->isAdmin()) {
                        abort(403);
                    }

                    $blockers = $record->accountDeletionBlockers();

                    if (!empty($blockers)) {
                        Notification::make()
                            ->title('Suppression impossible')
                            ->body(implode(' ', $blockers))
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->anonymizeAndDelete();

                    Notification::make()
                        ->title('Compte supprimé et anonymisé')
                        ->success()
                        ->send();

                    redirect($this->getResource()::getUrl('index'));
                }),
            RestoreAction::make(),
        ];
    }
}
