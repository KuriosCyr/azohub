<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Models\Faq;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->label('Catégorie')
                    ->options(Faq::categories())
                    ->required(),
                TextInput::make('question')
                    ->label('Question')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->label('Réponse')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->label('Ordre d\'affichage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Visible sur le site')
                    ->required()
                    ->default(true),
            ]);
    }
}
