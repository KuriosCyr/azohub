<?php

namespace App\Filament\Resources\ServiceRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ServiceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('budget')
                    ->label('Budget')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('deadline')
                    ->label('Délai (j)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Ouverte',
                        'closed' => 'Clôturée',
                        'cancelled' => 'Annulée',
                        default => $state,
                    }),
                TextColumn::make('proposals_count')
                    ->label('Propositions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'open' => 'Ouverte',
                        'closed' => 'Clôturée',
                        'cancelled' => 'Annulée',
                    ]),
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
