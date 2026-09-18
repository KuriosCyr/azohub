<?php

namespace App\Filament\Resources\Advertisements\Tables;

use App\Models\Advertisement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Campagne')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('advertiser_name')
                    ->label('Annonceur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('placement')
                    ->label('Emplacement')
                    ->formatStateUsing(fn (string $state): string => Advertisement::placements()[$state] ?? $state)
                    ->badge()
                    ->color('info'),
                TextColumn::make('starts_at')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Advertisement $record): string => $record->ends_at->isPast() ? 'danger' : 'gray'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('impressions')
                    ->label('Vues')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('clicks')
                    ->label('Clics')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price_paid')
                    ->label('Facturé')
                    ->money('XOF')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('placement')
                    ->label('Emplacement')
                    ->options(Advertisement::placements()),
                TernaryFilter::make('is_active')
                    ->label('Active'),
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
