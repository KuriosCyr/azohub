<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('slug')
                    ->label('Identifiant')
                    ->helperText('gratuit, pro ou premium : ces identifiants sont utilisés par le code, ne les changez pas.')
                    ->required(),
                Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Prix mensuel')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('FCFA'),
                TextInput::make('yearly_price')
                    ->label('Prix annuel')
                    ->numeric()
                    ->suffix('FCFA')
                    ->helperText("Laisser vide pour ne pas proposer d'offre annuelle."),
                TextInput::make('billing_period')
                    ->label('Période de base')
                    ->required()
                    ->default('monthly')
                    ->helperText('Laisser « monthly » : la facturation annuelle est gérée par le prix annuel.'),
                TextInput::make('max_services')
                    ->label('Nombre max. de services')
                    ->numeric()
                    ->helperText('Laisser vide pour illimité.'),
                TextInput::make('commission_rate')
                    ->label('Commission (%)')
                    ->required()
                    ->numeric()
                    ->default(15)
                    ->suffix('%'),
                TagsInput::make('features')
                    ->label('Avantages affichés')
                    ->helperText('Tapez un avantage puis Entrée.')
                    ->columnSpanFull(),
                Toggle::make('is_popular')
                    ->label('Mis en avant (« Populaire »)'),
                Toggle::make('is_active')
                    ->label('Plan actif')
                    ->default(true),
                TextInput::make('order')
                    ->label("Ordre d'affichage")
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
