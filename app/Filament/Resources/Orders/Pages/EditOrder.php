<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

// Pas de ForceDeleteAction (audit externe) : une suppression définitive d'une commande efface
// en cascade ses paiements, litiges, messages et avis — l'historique financier et le dossier du
// litige ne doivent jamais pouvoir disparaître, même par erreur. La suppression douce
// (DeleteAction, réversible via RestoreAction) reste possible.
class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
