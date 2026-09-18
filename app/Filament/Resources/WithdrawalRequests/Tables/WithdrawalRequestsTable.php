<?php

namespace App\Filament\Resources\WithdrawalRequests\Tables;

use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalRequestPaid;
use App\Notifications\WithdrawalRequestRejected;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequestsTable
{
    private const METHOD_LABELS = [
        'mtn_momo' => 'MTN MoMo',
        'moov_money' => 'Moov Money',
        'celtiis_cash' => 'Celtiis Cash',
    ];

    private const STATUS_LABELS = [
        'pending' => 'En attente',
        'paid' => 'Payé',
        'rejected' => 'Refusé',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prestataire.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Mode')
                    ->formatStateUsing(fn (string $state): string => self::METHOD_LABELS[$state] ?? $state),
                TextColumn::make('phone_number')
                    ->label('Numéro'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => self::STATUS_LABELS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Demandé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('processedBy.name')
                    ->label('Traité par')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::STATUS_LABELS)
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label('Marquer comme payé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WithdrawalRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('Confirmez-vous avoir envoyé les fonds via mobile money à ce prestataire ?')
                    ->action(function (WithdrawalRequest $record) {
                        $record->markAsPaid(Auth::id());
                        $record->prestataire->notify(new WithdrawalRequestPaid($record));

                        Notification::make()
                            ->title('Retrait de ' . $record->prestataire->name . ' marqué comme payé')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WithdrawalRequest $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif du refus')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (WithdrawalRequest $record, array $data) {
                        $record->reject(Auth::id(), $data['reason']);
                        $record->prestataire->notify(new WithdrawalRequestRejected($record));

                        Notification::make()
                            ->title('Retrait de ' . $record->prestataire->name . ' refusé, solde recrédité')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
