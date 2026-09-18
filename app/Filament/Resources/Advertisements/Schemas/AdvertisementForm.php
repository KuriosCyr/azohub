<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Models\Advertisement;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Annonceur')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre (repère interne)')
                            ->required()
                            ->placeholder('Ex : Campagne rentrée — Boutique X'),
                        TextInput::make('advertiser_name')
                            ->label('Nom de l\'entreprise')
                            ->required(),
                        TextInput::make('advertiser_contact')
                            ->label('Contact (email / téléphone)'),
                        TextInput::make('price_paid')
                            ->label('Montant facturé (FCFA)')
                            ->numeric(),
                    ])
                    ->columns(2),

                Section::make('Annonce')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Image de la bannière')
                            ->image()
                            ->directory('advertisements')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('link_url')
                            ->label('Lien de destination')
                            ->url()
                            ->required()
                            ->placeholder('https://...')
                            ->columnSpanFull(),
                        Select::make('placement')
                            ->label('Emplacement')
                            ->options(Advertisement::placements())
                            ->default('home_banner')
                            ->required(),
                        TextInput::make('order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->helperText('Si plusieurs annonces actives sur le même emplacement, la plus petite valeur passe en premier.'),
                    ])
                    ->columns(2),

                Section::make('Diffusion')
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('Début')
                            ->required()
                            ->default(now()),
                        DatePicker::make('ends_at')
                            ->label('Fin')
                            ->required()
                            ->default(now()->addDays(30)),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Notes internes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
