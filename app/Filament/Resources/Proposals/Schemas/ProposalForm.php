<?php

namespace App\Filament\Resources\Proposals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Proposition')
                    ->schema([
                        Select::make('service_request_id')
                            ->label('Demande')
                            ->relationship('serviceRequest', 'title')
                            ->searchable()
                            ->required(),
                        Select::make('user_id')
                            ->label('Prestataire')
                            ->relationship('prestataire', 'name')
                            ->searchable()
                            ->required(),
                        Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('proposed_price')
                            ->label('Prix proposé (FCFA)')
                            ->required()
                            ->numeric(),
                        TextInput::make('delivery_time')
                            ->label('Délai de livraison (jours)')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(2),

                Section::make('Statut')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'accepted' => 'Acceptée',
                                'rejected' => 'Refusée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('accepted_at')
                            ->label('Acceptée le'),
                    ])
                    ->columns(2),
            ]);
    }
}
