<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->label('Commande')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('reviewer_id')
                    ->label('Auteur')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('reviewee_id')
                    ->label('Destinataire')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('service_id')
                    ->label('Service')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('rating')
                    ->label('Note globale')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->label('Commentaire')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('quality_rating')
                    ->label('Qualité')
                    ->numeric(),
                TextInput::make('communication_rating')
                    ->label('Communication')
                    ->numeric(),
                TextInput::make('timeliness_rating')
                    ->label('Respect des délais')
                    ->numeric(),
                TextInput::make('clarity_rating')
                    ->label('Clarté (avis prestataire→client)')
                    ->numeric(),
                TextInput::make('responsiveness_rating')
                    ->label('Réactivité (avis prestataire→client)')
                    ->numeric(),
                Select::make('review_type')
                    ->label('Type d\'avis')
                    ->options([
                        'client_to_prestataire' => 'Client vers prestataire',
                        'prestataire_to_client' => 'Prestataire vers client',
                    ])
                    ->disabled()
                    ->dehydrated(false)
                    ->required(),
                Toggle::make('is_visible')
                    ->label('Visible publiquement')
                    ->helperText('Décocher masque l\'avis du profil et du service ; la note moyenne est recalculée automatiquement.')
                    ->required(),
            ]);
    }
}
