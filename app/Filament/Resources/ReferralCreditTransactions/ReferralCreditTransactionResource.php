<?php

namespace App\Filament\Resources\ReferralCreditTransactions;

use App\Filament\Resources\ReferralCreditTransactions\Pages\ListReferralCreditTransactions;
use App\Filament\Resources\ReferralCreditTransactions\Tables\ReferralCreditTransactionsTable;
use App\Models\ReferralCreditTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Lecture seule : ce journal n'est jamais créé, modifié ni supprimé à la main — seul
// User::maybeRewardReferrer()/redeemReferralCredit()/refundReferralCredit() y écrit (voir ce
// modèle). Corrige un point signalé par un audit externe : le crédit de parrainage n'était ni
// journalisé ni visible dans l'admin, contrairement au portefeuille réel.
class ReferralCreditTransactionResource extends Resource
{
    protected static ?string $model = ReferralCreditTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static string|\UnitEnum|null $navigationGroup = 'Paiements';
    protected static ?string $navigationLabel = 'Registre du parrainage';
    protected static ?string $modelLabel = 'Mouvement de crédit parrainage';
    protected static ?string $pluralModelLabel = 'Mouvements de crédit parrainage';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ReferralCreditTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferralCreditTransactions::route('/'),
        ];
    }
}
