<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
                    ->label('Nom')
                    ->required(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('E-mail vérifié le'),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                    ->helperText('À la modification, laissez vide pour conserver le mot de passe actuel.'),
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel(),
                TextInput::make('avatar')
                    ->label('Photo de profil (chemin)'),
                Select::make('role')
                    ->label('Rôle')
                    ->options(['client' => 'Client', 'prestataire' => 'Prestataire', 'admin' => 'Administrateur'])
                    ->default('client')
                    ->required(),
                Textarea::make('bio')
                    ->label('Présentation')
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->label('Ville'),
                TagsInput::make('service_areas')
                    ->label("Zones d'intervention"),
                TagsInput::make('languages')
                    ->label('Langues parlées'),
                TextInput::make('availability')
                    ->label('Disponibilité')
                    ->required()
                    ->default('disponible'),
                TextInput::make('rating')
                    ->label('Note moyenne')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_reviews')
                    ->label("Nombre d'avis")
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('completed_orders')
                    ->label('Commandes terminées')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('level')
                    ->label('Niveau')
                    ->options(['nouveau' => 'Nouveau', 'confirme' => 'Confirmé', 'expert' => 'Expert'])
                    ->default('nouveau')
                    ->required(),
                TagsInput::make('badges')
                    ->label('Badges'),
                Toggle::make('identity_verified')
                    ->label('Identité vérifiée'),
                TextInput::make('identity_document')
                    ->label("Pièce d'identité (chemin)"),
                TextInput::make('wallet_balance')
                    ->label('Solde du portefeuille')
                    ->numeric()
                    ->default(0.0)
                    ->suffix('FCFA')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Lecture seule : ce solde ne doit être modifié que par le déroulement normal des commandes et des retraits, jamais manuellement.'),
                Toggle::make('is_active')
                    ->label('Compte actif')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Lecture seule : utilisez les actions « Désactiver »/« Réactiver » de la liste pour changer ce statut (le compte reçoit un motif et une notification).'),
                DateTimePicker::make('last_seen_at')
                    ->label('Dernière connexion'),
            ]);
    }
}
