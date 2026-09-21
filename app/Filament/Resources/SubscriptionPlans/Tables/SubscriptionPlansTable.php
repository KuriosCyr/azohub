<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Identifiant')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label('Prix / mois')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('yearly_price')
                    ->label('Prix / an')
                    ->formatStateUsing(fn ($state) => $state === null ? '—' : number_format((float) $state, 0, ',', ' ') . ' FCFA'),
                TextColumn::make('max_services')
                    ->label('Services max.')
                    ->formatStateUsing(fn ($state) => $state === null ? 'Illimité' : $state)
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 2, ',', ''), '0'), ',') . ' %')
                    ->sortable(),
                IconColumn::make('is_popular')
                    ->label('Populaire')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
