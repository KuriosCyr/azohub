<?php

namespace App\Filament\Resources\PromoCodes\Pages;

use App\Filament\Resources\PromoCodes\PromoCodeResource;
use Filament\Resources\Pages\EditRecord;

// Pas de DeleteAction (audit externe) : supprimer un code promo effacerait en cascade son
// historique d'utilisation, le rendant de nouveau utilisable par tout le monde. Désactiver
// (is_active) est la seule façon de rendre un code inutilisable — protégé aussi au niveau base
// de données (voir la migration restrict_promo_code_deletion_with_redemptions).
class EditPromoCode extends EditRecord
{
    protected static string $resource = PromoCodeResource::class;
}
