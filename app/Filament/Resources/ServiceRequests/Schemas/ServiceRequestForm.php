<?php

namespace App\Filament\Resources\ServiceRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ServiceRequestForm
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
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('budget')
                    ->numeric(),
                TextInput::make('deadline')
                    ->numeric(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('address'),
                TextInput::make('attachments'),
                Select::make('status')
                    ->options(['open' => 'Open', 'closed' => 'Closed', 'cancelled' => 'Cancelled'])
                    ->default('open')
                    ->required(),
                TextInput::make('proposals_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('expires_at'),
            ]);
    }
}
