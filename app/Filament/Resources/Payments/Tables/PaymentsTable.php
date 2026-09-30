<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public const METHODS = [
        'mtn_momo' => 'MTN MoMo',
        'moov_money' => 'Moov Money',
        'celtiis_cash' => 'Celtiis Cash',
        'card' => 'Carte bancaire',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'success' => 'Réussi',
        'failed' => 'Échoué',
        'refund_pending' => 'Remboursement à traiter',
        'refunded' => 'Remboursé',
    ];

    public const TYPES = [
        'order_payment' => 'Paiement de commande',
        'subscription' => 'Abonnement',
        'withdrawal' => 'Retrait',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state),
                TextColumn::make('order.order_number')
                    ->label('Commande')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Payeur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('refund_amount_due')
                    ->label('Dû au remboursement')
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 0, ',', ' ') . ' FCFA' : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_method')
                    ->label('Moyen de paiement')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::METHODS[$state] ?? $state),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')
                    ->label('Payé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('transaction_id')
                    ->label('N° de transaction')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone_number')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gateway_reference')
                    ->label('Référence FedaPay')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::STATUSES),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(self::TYPES),
            ])
            ->recordActions([
                // Indépendant de Order::confirmRefund() (audit externe — 2e audit) : un paiement
                // "orphelin" n'a pas forcément d'ordre en payment_status='refund_pending' (ex.
                // commande déjà annulée autrement, ou abonnement sans commande associée) — sans
                // cette action, ces lignes restaient indéfiniment dans "Remboursements à traiter"
                // sans aucun moyen de les résoudre.
                Action::make('confirm_refund')
                    ->label('Confirmer remboursement')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (Payment $record) => $record->status === 'refund_pending')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer le remboursement')
                    ->modalDescription('FedaPay ne propose pas de remboursement automatique : confirmez uniquement après avoir traité ce remboursement manuellement depuis le dashboard FedaPay (Transactions → Rembourser).')
                    ->action(function (Payment $record) {
                        $record->confirmRefund();

                        Notification::make()
                            ->title('Remboursement confirmé')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
            ]);
    }
}
