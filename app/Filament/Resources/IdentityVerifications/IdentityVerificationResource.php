<?php

namespace App\Filament\Resources\IdentityVerifications;

use App\Filament\Resources\IdentityVerifications\Pages\ListIdentityVerifications;
use App\Filament\Resources\IdentityVerifications\Tables\IdentityVerificationsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IdentityVerificationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'identity-verifications';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static string|\UnitEnum|null $navigationGroup = 'Utilisateurs';
    protected static ?string $navigationLabel = 'Vérifications d\'identité';
    protected static ?string $modelLabel = 'Vérification';
    protected static ?string $pluralModelLabel = 'Vérifications d\'identité';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return IdentityVerificationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('identity_document');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdentityVerifications::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('identity_verification_status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
