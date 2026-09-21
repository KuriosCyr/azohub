<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public const STATUSES = [
        'pending' => 'En attente de paiement',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
    ];

    public const PERIODS = [
        'monthly' => 'Mensuel',
        'yearly' => 'Annuel',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->sortable(),
                TextColumn::make('billing_period')
                    ->label('Facturation')
                    ->formatStateUsing(fn (?string $state) => self::PERIODS[$state] ?? $state),
                IconColumn::make('is_trial')
                    ->label('Mois offert')
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::STATUSES),
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
