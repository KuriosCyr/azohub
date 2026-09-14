<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('subscription_plan_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                Select::make('status')
                    ->options(['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'])
                    ->default('active')
                    ->required(),
            ]);
    }
}
