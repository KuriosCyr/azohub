<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Dernières commandes')
            ->query(
                Order::query()
                    ->with(['client', 'prestataire', 'service'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prestataire.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.title')
                    ->label('Service')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('XOF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'paid'            => 'primary',
                        'in_progress'     => 'info',
                        'delivered'       => 'warning',
                        'completed'       => 'success',
                        'cancelled'       => 'danger',
                        'disputed'        => 'danger',
                        default           => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_payment' => 'En attente paiement',
                        'paid'            => 'Payée',
                        'in_progress'     => 'En cours',
                        'delivered'       => 'Livrée',
                        'completed'       => 'Terminée',
                        'cancelled'       => 'Annulée',
                        'disputed'        => 'Litige',
                        default           => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date création')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record)),
            ]);
    }
}
