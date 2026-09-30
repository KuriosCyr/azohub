<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

// Pas de ForceDeleteAction (audit externe, comme pour les commandes) : une suppression
// définitive effacerait en cascade les paiements de cet abonnement. La suppression douce
// (DeleteAction, réversible via RestoreAction) reste possible.
class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
