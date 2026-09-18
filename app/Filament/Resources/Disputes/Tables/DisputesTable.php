<?php

namespace App\Filament\Resources\Disputes\Tables;

use App\Models\Dispute;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DisputesTable
{
    private const REASON_LABELS = [
        'work_not_delivered' => 'Travail non livré',
        'work_not_conform' => 'Travail non conforme',
        'poor_quality' => 'Mauvaise qualité',
        'late_delivery' => 'Livraison en retard',
        'payment_issue' => 'Problème de paiement',
        'other' => 'Autre',
    ];

    private const STATUS_LABELS = [
        'open' => 'Ouvert',
        'under_review' => 'En examen',
        'resolved' => 'Résolu',
        'cancelled' => 'Annulé',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Commande')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('openedBy.name')
                    ->label('Ouvert par')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Raison')
                    ->formatStateUsing(fn (string $state): string => self::REASON_LABELS[$state] ?? $state)
                    ->badge()
                    ->color('warning'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => self::STATUS_LABELS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'under_review' => 'warning',
                        'resolved' => 'success',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('resolvedBy.name')
                    ->label('Résolu par')
                    ->toggleable(),
                TextColumn::make('resolved_at')
                    ->label('Résolu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Ouvert le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::STATUS_LABELS)
                    ->default('open'),
                SelectFilter::make('reason')
                    ->label('Raison')
                    ->options(self::REASON_LABELS),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Voir'),

                Action::make('mark_under_review')
                    ->label('Examiner')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Dispute $record) => $record->status === 'open')
                    ->requiresConfirmation()
                    ->action(function (Dispute $record) {
                        $record->update(['status' => 'under_review']);
                    }),

                Action::make('resolve')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Dispute $record) => in_array($record->status, ['open', 'under_review']))
                    ->form([
                        Select::make('resolution')
                            ->label('Décision')
                            ->options([
                                'refund_client' => 'Rembourser le client (commande annulée)',
                                'pay_prestataire' => 'Payer le prestataire (commande complétée)',
                                'partial_refund' => 'Remboursement partiel (à traiter manuellement)',
                                'no_action' => 'Aucune action (litige non fondé)',
                            ])
                            ->required(),
                        Textarea::make('admin_note')
                            ->label('Note / justification')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (Dispute $record, array $data) {
                        $record->admin_note = $data['admin_note'];
                        $record->save();

                        $record->resolve($data['resolution'], Auth::id());

                        Notification::make()
                            ->title('Litige résolu pour la commande ' . $record->order->order_number)
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
