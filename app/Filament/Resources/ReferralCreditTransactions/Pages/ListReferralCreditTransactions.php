<?php

namespace App\Filament\Resources\ReferralCreditTransactions\Pages;

use App\Filament\Resources\ReferralCreditTransactions\ReferralCreditTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListReferralCreditTransactions extends ListRecords
{
    protected static string $resource = ReferralCreditTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
