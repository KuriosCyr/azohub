<?php

namespace App\Filament\Resources\Proposals;

use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\Proposals\Pages\ListProposals;
use App\Filament\Resources\Proposals\Schemas\ProposalForm;
use App\Filament\Resources\Proposals\Tables\ProposalsTable;
use App\Models\Proposal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'Services';
    protected static ?string $navigationLabel = 'Propositions';
    protected static ?string $modelLabel = 'Proposition';
    protected static ?string $pluralModelLabel = 'Propositions';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ProposalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposals::route('/'),
            'create' => CreateProposal::route('/create'),
            'edit' => EditProposal::route('/{record}/edit'),
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
