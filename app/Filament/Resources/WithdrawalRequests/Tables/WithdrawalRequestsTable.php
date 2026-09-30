<?php

namespace App\Filament\Resources\WithdrawalRequests\Tables;

use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalRequestPaid;
use App\Notifications\WithdrawalRequestRejected;
use App\Services\PaymentService;
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
                TextColumn::make('fedapay_status')
                    ->label('FedaPay')
                    ->badge()
                    ->placeholder('—')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'initiating' => 'Bloqué (à vérifier)',
                        'pending' => 'Virement en cours',
                        'sent' => 'Envoyé',
                        'failed' => 'Échoué',
                        default => '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'sent' => 'success',
                        'pending' => 'warning',
                        'failed', 'initiating' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
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
                Action::make('pay_via_fedapay')
                    ->label('Payer via FedaPay')
                    ->icon('heroicon-o-bolt')
                    ->color('primary')
                    ->visible(fn (WithdrawalRequest $record) => $record->canRetryFedapayPayout())
                    ->requiresConfirmation()
                    ->modalDescription('Déclenche un virement Mobile Money réel via l\'API FedaPay. La demande ne sera marquée "payée" qu\'une fois FedaPay confirmé (peut prendre quelques instants) — vous pouvez fermer cette page entre-temps.')
                    ->action(function (WithdrawalRequest $record) {
                        try {
                            app(PaymentService::class)->initiatePayout($record);

                            Notification::make()
                                ->title('Virement FedaPay déclenché pour ' . $record->prestataire->name)
                                ->body('En attente de confirmation FedaPay.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            report($e);

                            $record->refresh();

                            Notification::make()
                                ->title('Échec du déclenchement FedaPay')
                                ->body($record->fedapay_payout_id === null
                                    ? 'Rien n\'a été créé côté FedaPay. Vous pouvez réessayer, ou traiter manuellement ("Marquer comme payé").'
                                    : 'Un virement a peut-être été envoyé malgré l\'erreur — vérifiez le tableau de bord FedaPay avant toute nouvelle action. La demande reste bloquée tant que ce n\'est pas vérifié (voir "Débloquer manuellement").')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                Action::make('clear_ambiguous_fedapay')
                    ->label('Débloquer manuellement')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    // 'initiating' inclus (audit externe — 2e audit) : sans ça, une demande dont
                    // le processus a planté juste après la réservation (avant d'obtenir un
                    // identifiant FedaPay) restait bloquée pour toujours, sans aucune action
                    // disponible pour la débloquer.
                    // Délai de 10 min sur 'initiating' seul, sans identifiant (audit externe — 3e
                    // audit) : resolveFedapayCustomerId() peut légitimement tourner plusieurs
                    // dizaines de secondes (jusqu'à 20 pages) — sans ce délai, un admin pressé
                    // pouvait débloquer un essai simplement lent (pas mort) et en déclencher un
                    // second en parallèle, alors que le premier, toujours actif, finit par envoyer
                    // lui aussi (voir WithdrawalRequest::markFedapayPayoutSent(), qui refuse
                    // maintenant d'envoyer si débloqué entre-temps — ce délai reste une seconde
                    // barrière pour éviter même de tenter ce déblocage trop tôt). Un identifiant déjà
                    // obtenu (fedapay_payout_id !== null) signifie que ce process est déjà terminé
                    // (réussi ou en erreur), donc pas de fenêtre de course à protéger dans ce cas.
                    ->visible(fn (WithdrawalRequest $record) => $record->status === 'pending'
                        && $record->fedapay_status !== 'sent'
                        && (
                            $record->fedapay_payout_id !== null
                            || ($record->fedapay_status === 'initiating' && $record->updated_at?->lt(now()->subMinutes(10)))
                        ))
                    ->requiresConfirmation()
                    ->modalDescription('À utiliser UNIQUEMENT après avoir vérifié sur le tableau de bord FedaPay que ce virement n\'a PAS été envoyé. Débloquer sans vérifier risque un double paiement si l\'argent est en réalité déjà parti.')
                    ->action(function (WithdrawalRequest $record) {
                        $record->clearAmbiguousFedapayAttempt();

                        Notification::make()
                            ->title('Demande débloquée pour ' . $record->prestataire->name)
                            ->success()
                            ->send();
                    }),

                Action::make('mark_paid')
                    ->label('Marquer comme payé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WithdrawalRequest $record) => $record->status === 'pending' && $record->fedapay_payout_id === null)
                    ->requiresConfirmation()
                    ->modalDescription('Confirmez-vous avoir envoyé les fonds via mobile money à ce prestataire ?')
                    ->action(function (WithdrawalRequest $record, $livewire) {
                        $record->markAsPaid(Auth::id());
                        $record->prestataire->notify(new WithdrawalRequestPaid($record));

                        Notification::make()
                            ->title('Retrait de ' . $record->prestataire->name . ' marqué comme payé')
                            ->success()
                            ->send();

                        // Le badge "Retraits" de la sidebar n'est recalculé qu'au chargement
                        // complet d'une page : sans cet événement, il reste affiché tel quel
                        // jusqu'à ce que l'admin recharge manuellement.
                        $livewire->dispatch('refresh-sidebar');
                    }),

                Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WithdrawalRequest $record) => $record->status === 'pending' && $record->fedapay_payout_id === null)
                    ->form([
                        Textarea::make('reason')
                            ->label('Motif du refus')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (WithdrawalRequest $record, array $data, $livewire) {
                        $record->reject(Auth::id(), $data['reason']);
                        $record->prestataire->notify(new WithdrawalRequestRejected($record));

                        Notification::make()
                            ->title('Retrait de ' . $record->prestataire->name . ' refusé, solde recrédité')
                            ->warning()
                            ->send();

                        $livewire->dispatch('refresh-sidebar');
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
