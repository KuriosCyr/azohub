<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(32)
                    ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper(trim($state)) : $state)
                    ->helperText('Le client le saisit exactement (insensible à la casse) au paiement.'),
                Select::make('type')
                    ->label('Type de réduction')
                    ->options([
                        'fixed' => 'Montant fixe (FCFA)',
                        'percentage' => 'Pourcentage (%)',
                    ])
                    ->default('fixed')
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label(fn (Get $get) => $get('type') === 'percentage' ? 'Pourcentage de réduction' : 'Montant de la réduction (FCFA)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(fn (Get $get) => $get('type') === 'percentage' ? 100 : null)
                    ->required(),
                TextInput::make('max_uses')
                    ->label("Nombre maximum d'utilisations")
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('Laisser vide pour un usage illimité (toujours limité à une fois par client).'),
                DateTimePicker::make('expires_at')
                    ->label("Date d'expiration")
                    ->nullable()
                    ->helperText('Laisser vide pour un code sans date limite.'),
                TextInput::make('min_order_amount')
                    ->label('Montant minimum de commande (FCFA)')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->helperText('Laisser vide pour appliquer le code sans montant minimum.'),
                Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true)
                    ->helperText('Désactiver un code le rend immédiatement inutilisable sans le supprimer (conserve son historique).'),
            ]);
    }
}
