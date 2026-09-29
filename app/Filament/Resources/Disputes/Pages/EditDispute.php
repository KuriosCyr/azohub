<?php

namespace App\Filament\Resources\Disputes\Pages;

use App\Filament\Resources\Disputes\DisputeResource;
use Filament\Resources\Pages\EditRecord;

// Pas de DeleteAction (audit externe) : un litige supprimé laisserait sa commande bloquée en
// 'disputed' pour toujours, l'argent restant gelé sans aucun moyen de le débloquer. Un litige
// est un dossier permanent, comme un paiement — jamais supprimable, seulement résolu.
class EditDispute extends EditRecord
{
    protected static string $resource = DisputeResource::class;
}
