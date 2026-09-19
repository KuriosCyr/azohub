<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('avatar'),
                Select::make('role')
                    ->options(['client' => 'Client', 'prestataire' => 'Prestataire', 'admin' => 'Admin'])
                    ->default('client')
                    ->required(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('city'),
                TextInput::make('service_areas'),
                TextInput::make('languages'),
                TextInput::make('availability')
                    ->required()
                    ->default('disponible'),
                TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_reviews')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('completed_orders')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('level')
                    ->options(['nouveau' => 'Nouveau', 'confirme' => 'Confirme', 'expert' => 'Expert'])
                    ->default('nouveau')
                    ->required(),
                TextInput::make('badges'),
                Toggle::make('identity_verified')
                    ->required(),
                TextInput::make('identity_document'),
                TextInput::make('wallet_balance')
                    ->numeric()
                    ->default(0.0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Lecture seule : ce solde ne doit être modifié que par le déroulement normal des commandes et des retraits, jamais manuellement.'),
                Toggle::make('is_active')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Lecture seule : utilisez les actions « Désactiver »/« Réactiver » de la liste pour changer ce statut (le compte reçoit un motif et une notification).'),
                DateTimePicker::make('last_seen_at'),
            ]);
    }
}
