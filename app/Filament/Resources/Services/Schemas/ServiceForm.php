<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('what_included')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('price_type')
                    ->options(['fixe' => 'Fixe', 'a_partir_de' => 'A partir de'])
                    ->default('fixe')
                    ->required(),
                TextInput::make('delivery_time')
                    ->required()
                    ->numeric(),
                TextInput::make('city'),
                Textarea::make('service_area')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->image(),
                TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_orders')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_reviews')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'active' => 'Active', 'paused' => 'Paused', 'rejected' => 'Rejected'])
                    ->default('active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
            ]);
    }
}
