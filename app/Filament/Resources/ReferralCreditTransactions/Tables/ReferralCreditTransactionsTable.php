<?php

namespace App\Filament\Resources\ReferralCreditTransactions\Tables;

use App\Models\Order;
use App\Models\ReferralCreditTransaction;
use App\Models\Subscription;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReferralCreditTransactionsTable
{
    private const TYPES = [
        'earned' => 'Gagné',
        'redeemed' => 'Dépensé',
        'refunded' => 'Restitué',
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
                    ->label('Parrain')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'earned', 'refunded' => 'success',
                        'redeemed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn (ReferralCreditTransaction $record) => ($record->type === 'redeemed' ? '-' : '+') . number_format((float) $record->amount, 0, ',', ' ') . ' FCFA')
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
                        Order::class => 'Commande',
                        Subscription::class => 'Abonnement',
                        User::class => 'Filleul',
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
