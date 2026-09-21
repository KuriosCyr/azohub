<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Notifications\AccountStatusChanged;
use App\Notifications\AdminWarning;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'client' => 'Client',
                        'prestataire' => 'Prestataire',
                        'admin' => 'Administrateur',
                        default => $state,
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'admin' => 'danger',
                        'prestataire' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable(),
                TextColumn::make('level')
                    ->label('Niveau')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'nouveau' => 'Nouveau',
                        'confirme' => 'Confirmé',
                        'expert' => 'Expert',
                        default => $state,
                    }),
                IconColumn::make('identity_verified')
                    ->label('Identité vérifiée')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Compte actif')
                    ->boolean(),
                TextColumn::make('wallet_balance')
                    ->label('Solde du portefeuille')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Note')
                    ->numeric(1)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_reviews')
                    ->label('Avis')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_orders')
                    ->label('Commandes terminées')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('availability')
                    ->label('Disponibilité')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->label('E-mail vérifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_seen_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'client' => 'Client',
                        'prestataire' => 'Prestataire',
                        'admin' => 'Administrateur',
                    ]),
            ])
            ->recordActions([
                Action::make('warn')
                    ->label('Avertir')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->form([
                        TextInput::make('subject')
                            ->label('Sujet')
                            ->required()
                            ->maxLength(150),
                        Textarea::make('body')
                            ->label('Message')
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->notify(new AdminWarning($data['subject'], $data['body']));

                        Notification::make()
                            ->title('Avertissement envoyé à ' . $record->name)
                            ->success()
                            ->send();
                    }),

                Action::make('deactivate')
                    ->label('Désactiver')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->is_active)
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif de la désactivation')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['is_active' => false]);
                        $record->notify(new AccountStatusChanged(false, $data['reason']));

                        Notification::make()
                            ->title('Compte de ' . $record->name . ' désactivé')
                            ->success()
                            ->send();
                    }),

                Action::make('reactivate')
                    ->label('Réactiver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => !$record->is_active)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['is_active' => true]);
                        $record->notify(new AccountStatusChanged(true));

                        Notification::make()
                            ->title('Compte de ' . $record->name . ' réactivé')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('warn_selection')
                        ->label('Avertir la sélection')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->form([
                            TextInput::make('subject')
                                ->label('Sujet')
                                ->required()
                                ->maxLength(150),
                            Textarea::make('body')
                                ->label('Message')
                                ->required()
                                ->rows(5),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $record->notify(new AdminWarning($data['subject'], $data['body']));
                            }

                            Notification::make()
                                ->title('Message envoyé à ' . $records->count() . ' utilisateur(s)')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
