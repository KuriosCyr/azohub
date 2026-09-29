<?php

namespace App\Filament\Resources\WalletTransactions;

use App\Filament\Resources\WalletTransactions\Pages\ListWalletTransactions;
use App\Filament\Resources\WalletTransactions\Tables\WalletTransactionsTable;
use App\Models\WalletTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

// Lecture seule : ce journal n'est jamais créé, modifié ni supprimé à la main — seul
// User::creditWallet()/debitWallet() y écrit (voir ce modèle). Répond au besoin de pouvoir
// reconstituer l'historique d'un portefeuille en cas d'écart constaté ou de litige.
class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Paiements';
    protected static ?string $navigationLabel = 'Registre des portefeuilles';
    protected static ?string $modelLabel = 'Mouvement de portefeuille';
    protected static ?string $pluralModelLabel = 'Mouvements de portefeuille';
    protected static ?int $navigationSort = 2;

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
        return WalletTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWalletTransactions::route('/'),
        ];
    }
}
