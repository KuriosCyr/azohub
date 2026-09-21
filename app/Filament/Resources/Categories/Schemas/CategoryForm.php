<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('slug')
                    ->label('Lien (slug)')
                    ->required(),
                Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->label('Icône'),
                TextInput::make('color')
                    ->label('Couleur')
                    ->required()
                    ->default('#1E40AF'),
                Toggle::make('is_active')
                    ->label('Catégorie active')
                    ->default(true),
                TextInput::make('order')
                    ->label("Ordre d'affichage")
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
