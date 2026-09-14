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
                TextColumn::make('order_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewer_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewee_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quality_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('communication_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deadline_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('clarity_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('responsiveness_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('review_type')
                    ->badge(),
                IconColumn::make('is_visible')
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
