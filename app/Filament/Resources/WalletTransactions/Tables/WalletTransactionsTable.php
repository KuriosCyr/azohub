<?php

namespace App\Filament\Resources\WalletTransactions\Tables;

use App\Models\WalletTransaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsTable
{
    private const TYPES = [
        'credit' => 'Crédit',
        'debit' => 'Débit',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'credit' ? 'success' : 'danger'),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn (WalletTransaction $record) => ($record->type === 'credit' ? '+' : '-') . number_format((float) $record->amount, 0, ',', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->label('Solde après')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Motif')
                    ->wrap(),
                TextColumn::make('source_type')
                    ->label('Origine')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        \App\Models\Order::class => 'Commande',
                        \App\Models\WithdrawalRequest::class => 'Retrait',
                        null => '—',
                        default => class_basename($state),
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(self::TYPES),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
