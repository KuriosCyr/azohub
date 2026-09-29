<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// Pas de DeleteBulkAction (audit externe) : supprimer un code promo effacerait en cascade son
// historique d'utilisation. Désactiver (is_active) est la seule façon de rendre un code
// inutilisable — protégé aussi au niveau base de données.
class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => $state === 'percentage' ? 'Pourcentage' : 'Montant fixe'),
                TextColumn::make('value')
                    ->label('Réduction')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage'
                        ? number_format($state, 0, ',', ' ') . ' %'
                        : number_format($state, 0, ',', ' ') . ' FCFA'),
                TextColumn::make('redemptions_count')
                    ->label('Utilisations')
                    ->counts('redemptions')
                    ->formatStateUsing(fn ($state, $record) => $record->max_uses
                        ? "{$state} / {$record->max_uses}"
                        : (string) $state),
                TextColumn::make('min_order_amount')
                    ->label('Minimum')
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', ' ') . ' FCFA' : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
