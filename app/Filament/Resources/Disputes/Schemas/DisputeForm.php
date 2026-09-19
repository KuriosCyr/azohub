<?php

namespace App\Filament\Resources\Disputes\Schemas;

use App\Models\Dispute;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class DisputeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Litige')
                    ->schema([
                        Select::make('order_id')
                            ->label('Commande')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        Select::make('opened_by')
                            ->label('Ouvert par')
                            ->relationship('openedBy', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        Select::make('reason')
                            ->label('Raison')
                            ->options([
                                'work_not_delivered' => 'Travail non livré',
                                'work_not_conform' => 'Travail non conforme',
                                'poor_quality' => 'Mauvaise qualité',
                                'late_delivery' => 'Livraison en retard',
                                'payment_issue' => 'Problème de paiement',
                                'other' => 'Autre',
                            ])
                            ->required()
                            ->disabled(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'open' => 'Ouvert',
                                'under_review' => 'En examen',
                                'resolved' => 'Résolu',
                                'cancelled' => 'Annulé',
                            ])
                            ->default('open')
                            ->required()
                            ->disabled(),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->disabled()
                            ->columnSpanFull(),
                        Placeholder::make('evidences')
                            ->label('Preuves jointes')
                            ->content(function (Dispute $record) {
                                if (empty($record->evidences)) {
                                    return 'Aucune preuve jointe.';
                                }

                                $links = collect($record->evidences)->map(
                                    fn (array $file, int $index) => '<a href="' . e(route('disputes.evidence.download', [$record, $index])) . '" target="_blank" class="underline text-primary-600">' . e($file['name']) . '</a>'
                                )->implode('<br>');

                                return new HtmlString($links);
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Résolution')
                    ->description('Se renseigne uniquement via l\'action "Résoudre" du tableau — non modifiable directement ici.')
                    ->schema([
                        Select::make('resolution')
                            ->label('Résolution')
                            ->options([
                                'refund_client' => 'Remboursement client',
                                'pay_prestataire' => 'Paiement prestataire',
                                'partial_refund' => 'Remboursement partiel',
                                'no_action' => 'Aucune action',
                            ])
                            ->disabled(),
                        Select::make('resolved_by')
                            ->label('Résolu par')
                            ->relationship('resolvedBy', 'name')
                            ->disabled(),
                        DateTimePicker::make('resolved_at')
                            ->label('Résolu le')
                            ->disabled(),
                        Textarea::make('admin_note')
                            ->label('Note admin')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
