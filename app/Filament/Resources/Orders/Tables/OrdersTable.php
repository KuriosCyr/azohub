<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public const STATUSES = [
        'pending_payment' => 'En attente de paiement',
        'paid' => 'Payée',
        'in_progress' => 'En cours',
        'delivered' => 'Livrée',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
        'disputed' => 'En litige',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'En attente',
        'held' => 'Bloqué (escrow)',
        'released' => 'Libéré',
        'refund_pending' => 'Remboursement à traiter',
        'refunded' => 'Remboursé',
    ];

    private static function money($state): string
    {
        return number_format((float) $state, 0, ',', ' ') . ' FCFA';
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('N° de commande')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prestataire.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service.title')
                    ->label('Service')
                    // Une commande peut venir d'un service du catalogue (service_id renseigné)
                    // ou d'une demande négociée (devis accepté / offre personnalisée en chat) :
                    // dans ce second cas service_id est vide par conception, pas une anomalie —
                    // on retombe sur le titre de la demande plutôt que de laisser la cellule vide.
                    ->getStateUsing(fn ($record) => $record->service
                        ? $record->service->title
                        : $record->display_title . ' (demande négociée)')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),
                TextColumn::make('client_fee')
                    ->label('Frais client')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->sortable(),
                TextColumn::make('prestataire_amount')
                    ->label('Net prestataire')
                    ->formatStateUsing(fn ($state) => self::money($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'pending_payment' => 'gray',
                        'cancelled' => 'danger',
                        'disputed' => 'danger',
                        'delivered' => 'info',
                        default => 'warning',
                    }),
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
                    ->formatStateUsing(fn (string $state): string => self::PAYMENT_STATUSES[$state] ?? $state),
                TextColumn::make('delivery_time')
                    ->label('Délai (jours)')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expected_delivery_at')
                    ->label('Livraison prévue')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivered_at')
                    ->label('Livrée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('validation_deadline')
                    ->label('Validation avant le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('validated_at')
                    ->label('Validée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('auto_validated')
                    ->label('Validation auto.')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::STATUSES),
                SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options(self::PAYMENT_STATUSES),
                TrashedFilter::make(),
            ])
            ->recordActions([
                // Le remboursement se confirme désormais UNIQUEMENT depuis la fiche Paiement
                // (audit externe — 3e audit) : cette action confirmait TOUS les paiements
                // refund_pending de la commande en un seul clic, ce qui pouvait résoudre à tort un
                // paiement orphelin distinct (ex. double paiement jamais réellement remboursé)
                // simplement parce qu'il partageait la même commande. Voir Payment::confirmRefund(),
                // qui met désormais à jour order.payment_status lui-même une fois tous ses
                // paiements résolus.
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Pas de ForceDeleteBulkAction ici, volontairement : une commande porte un
                    // historique financier réel (montants, paiement, litige éventuel) que rien
                    // d'autre ne conserve — une suppression définitive serait irréversible et sans
                    // trace. Le "Delete" simple (SoftDeletes) reste possible et réversible via
                    // RestoreBulkAction.
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
