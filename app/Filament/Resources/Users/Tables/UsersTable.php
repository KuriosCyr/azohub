<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
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
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('avatar')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('availability')
                    ->searchable(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_reviews')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completed_orders')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level')
                    ->badge(),
                IconColumn::make('identity_verified')
                    ->boolean(),
                TextColumn::make('identity_document')
                    ->searchable(),
                TextColumn::make('wallet_balance')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('last_seen_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'client' => 'Client',
                        'prestataire' => 'Prestataire',
                        'admin' => 'Admin',
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
