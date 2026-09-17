<?php

namespace App\Filament\Resources\ServiceRequests\Schemas;

use App\Models\ServiceRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Demande')
                    ->schema([
                        Select::make('client_id')
                            ->label('Client')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Localisation & budget')
                    ->schema([
                        TextInput::make('city')
                            ->label('Ville')
                            ->required(),
                        TextInput::make('address')
                            ->label('Adresse précise'),
                        TextInput::make('budget')
                            ->label('Budget indicatif (FCFA)')
                            ->numeric(),
                        TextInput::make('deadline')
                            ->label('Délai souhaité (jours)')
                            ->numeric(),
                    ])
                    ->columns(2),

                Section::make('Statut & pièces jointes')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'open' => 'Ouverte',
                                'closed' => 'Clôturée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('open')
                            ->required(),
                        TextInput::make('proposals_count')
                            ->label('Nombre de propositions')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('expires_at')
                            ->label('Expire le'),
                        TextEntry::make('attachments_summary')
                            ->label('Pièces jointes')
                            ->state(fn (?ServiceRequest $record) => $record && !empty($record->attachments)
                                ? count($record->attachments) . ' fichier(s) — visibles uniquement côté client'
                                : 'Aucune pièce jointe'),
                    ])
                    ->columns(2),
            ]);
    }
}
