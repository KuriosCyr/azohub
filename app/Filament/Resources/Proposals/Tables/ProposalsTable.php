<?php

namespace App\Filament\Resources\Proposals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serviceRequest.title')
                    ->label('Demande')
                    ->searchable()
                    ->limit(35)
                    ->sortable(),
                TextColumn::make('prestataire.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposed_price')
                    ->label('Prix proposé')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('delivery_time')
                    ->label('Délai (j)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'accepted' => 'Acceptée',
                        'rejected' => 'Refusée',
                        'cancelled' => 'Annulée',
                        default => $state,
                    }),
                TextColumn::make('accepted_at')
                    ->label('Acceptée le')
                    ->dateTime('d/m/Y H:i')
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
                        'pending' => 'En attente',
                        'accepted' => 'Acceptée',
                        'rejected' => 'Refusée',
                        'cancelled' => 'Annulée',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
