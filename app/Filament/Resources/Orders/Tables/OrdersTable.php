<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prestataire_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service_request_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('proposal_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prestataire_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_time')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_delivery_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'held' => 'info',
                        'released' => 'success',
                        'refund_pending' => 'warning',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'held' => 'Bloqué (escrow)',
                        'released' => 'Libéré',
                        'refund_pending' => 'Remboursement à traiter',
                        'refunded' => 'Remboursé',
                        default => $state,
                    }),
                TextColumn::make('validation_deadline')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('validated_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('auto_validated')
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
                SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'pending' => 'En attente',
                        'held' => 'Bloqué (escrow)',
                        'released' => 'Libéré',
                        'refund_pending' => 'Remboursement à traiter',
                        'refunded' => 'Remboursé',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('confirm_refund')
                    ->label('Confirmer remboursement')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (Order $record) => $record->payment_status === 'refund_pending')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer le remboursement')
                    ->modalDescription('FedaPay ne propose pas de remboursement automatique : confirmez uniquement après avoir traité ce remboursement manuellement depuis le dashboard FedaPay (Transactions → Rembourser).')
                    ->action(function (Order $record) {
                        $record->confirmRefund();

                        Notification::make()
                            ->title('Remboursement confirmé pour la commande ' . $record->order_number)
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
