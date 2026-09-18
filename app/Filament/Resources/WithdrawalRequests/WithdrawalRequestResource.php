<?php

namespace App\Filament\Resources\WithdrawalRequests;

use App\Filament\Resources\WithdrawalRequests\Pages\ListWithdrawalRequests;
use App\Filament\Resources\WithdrawalRequests\Tables\WithdrawalRequestsTable;
use App\Models\WithdrawalRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class WithdrawalRequestResource extends Resource
{
    protected static ?string $model = WithdrawalRequest::class;

    protected static ?string $slug = 'withdrawal-requests';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Monétisation';
    protected static ?string $navigationLabel = 'Retraits';
    protected static ?string $modelLabel = 'Retrait';
    protected static ?string $pluralModelLabel = 'Retraits';

    public static function table(Table $table): Table
    {
        return WithdrawalRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWithdrawalRequests::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
