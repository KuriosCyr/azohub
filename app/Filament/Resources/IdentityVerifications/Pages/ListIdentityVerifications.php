<?php

namespace App\Filament\Resources\IdentityVerifications\Pages;

use App\Filament\Resources\IdentityVerifications\IdentityVerificationResource;
use Filament\Resources\Pages\ListRecords;

class ListIdentityVerifications extends ListRecords
{
    protected static string $resource = IdentityVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
