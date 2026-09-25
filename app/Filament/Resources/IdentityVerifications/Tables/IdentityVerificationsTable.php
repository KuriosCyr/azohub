<?php

namespace App\Filament\Resources\IdentityVerifications\Tables;

use App\Models\User;
use App\Notifications\IdentityVerificationReviewed;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdentityVerificationsTable
{
    private const STATUS_LABELS = [
        'none' => 'Aucune',
        'pending' => 'En attente',
        'verified' => 'Vérifiée',
        'rejected' => 'Rejetée',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                TextColumn::make('name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('identity_verification_status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => self::STATUS_LABELS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('identity_rejection_reason')
                    ->label('Motif du refus')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('identity_verification_status')
                    ->label('Statut')
                    ->options(self::STATUS_LABELS)
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('view_document')
                    ->label('Voir le document')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('gray')
                    ->url(fn (User $record) => route('identity-verification.show', $record))
                    ->openUrlInNewTab(),

                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->identity_verification_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (User $record, $livewire) {
                        $record->approveIdentityVerification();
                        $record->notify(new IdentityVerificationReviewed(true));

                        Notification::make()
                            ->title('Identité de ' . $record->name . ' vérifiée')
                            ->success()
                            ->send();

                        // Le badge "Vérifications d'identité" de la sidebar n'est recalculé
                        // qu'au chargement complet d'une page : sans cet événement, il reste
                        // affiché tel quel jusqu'à ce que l'admin recharge manuellement.
                        $livewire->dispatch('refresh-sidebar');
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->identity_verification_status === 'pending')
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif du refus')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data, $livewire) {
                        $record->rejectIdentityVerification($data['reason']);
                        $record->notify(new IdentityVerificationReviewed(false, $data['reason']));

                        Notification::make()
                            ->title('Identité de ' . $record->name . ' rejetée')
                            ->warning()
                            ->send();

                        $livewire->dispatch('refresh-sidebar');
                    }),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
