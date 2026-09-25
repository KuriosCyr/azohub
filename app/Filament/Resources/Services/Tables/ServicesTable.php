<?php

namespace App\Filament\Resources\Services\Tables;

use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (Service $record) => $record->title),
                TextColumn::make('prestataire.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Modération')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Service::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activé par le prestataire')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')
                    ->label('Sponsorisé')
                    ->boolean(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivery_time')
                    ->label('Délai (jours)')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rating')
                    ->label('Note')
                    ->numeric(1)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_orders')
                    ->label('Commandes')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_reviews')
                    ->label('Avis')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reviewed_at')
                    ->label('Modéré le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Modération')
                    ->options(Service::STATUS_LABELS),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Service $record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Approuver ce service ?')
                    ->modalDescription('Le service devient visible par les clients et le prestataire en est informé.')
                    ->action(function (Service $record, $livewire) {
                        $record->approve();

                        Notification::make()
                            ->title('Service « ' . $record->title . ' » approuvé')
                            ->success()
                            ->send();

                        // Le badge "Services" de la sidebar (compte les services "pending") n'est
                        // recalculé qu'au chargement complet d'une page : sans cet événement, il
                        // reste affiché tel quel jusqu'à ce que l'admin recharge manuellement.
                        $livewire->dispatch('refresh-sidebar');
                    }),
                Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Service $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif du refus (transmis au prestataire)')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (Service $record, array $data, $livewire) {
                        $record->reject($data['reason']);

                        Notification::make()
                            ->title('Service « ' . $record->title . ' » refusé')
                            ->success()
                            ->send();

                        $livewire->dispatch('refresh-sidebar');
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selection')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records, $livewire) {
                            $records->each(fn (Service $service) => $service->status !== 'active' && $service->approve());

                            Notification::make()
                                ->title('Services approuvés')
                                ->success()
                                ->send();

                            $livewire->dispatch('refresh-sidebar');
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
