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
                    ->required()
                    ->numeric(),
                TextInput::make('reviewer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('reviewee_id')
                    ->required()
                    ->numeric(),
                TextInput::make('service_id')
                    ->numeric(),
                TextInput::make('rating')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('quality_rating')
                    ->numeric(),
                TextInput::make('communication_rating')
                    ->numeric(),
                TextInput::make('deadline_rating')
                    ->numeric(),
                TextInput::make('clarity_rating')
                    ->numeric(),
                TextInput::make('responsiveness_rating')
                    ->numeric(),
                Select::make('review_type')
                    ->options([
            'client_to_prestataire' => 'Client to prestataire',
            'prestataire_to_client' => 'Prestataire to client',
        ])
                    ->required(),
                Toggle::make('is_visible')
                    ->required(),
            ]);
    }
}
