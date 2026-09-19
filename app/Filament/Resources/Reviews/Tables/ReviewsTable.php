<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewer.name')
                    ->label('Auteur')
                    ->searchable(),
                TextColumn::make('reviewee.name')
                    ->label('Destinataire')
                    ->searchable(),
                TextColumn::make('order.order_number')
                    ->label('Commande')
                    ->searchable(),
                TextColumn::make('rating')
                    ->label('Note')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comment')
                    ->label('Commentaire')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('review_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'client_to_prestataire' => 'Client → prestataire',
                        'prestataire_to_client' => 'Prestataire → client',
                        default => $state,
                    })
                    ->badge(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
