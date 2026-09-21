<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Filament\Resources\Subscriptions\Tables\SubscriptionsTable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Prestataire')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subscription_plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name')
                    ->preload()
                    ->required(),
                Select::make('billing_period')
                    ->label('Facturation')
                    ->options(SubscriptionsTable::PERIODS)
                    ->default('monthly')
                    ->required(),
                Toggle::make('is_trial')
                    ->label('Mois offert (essai)'),
                DateTimePicker::make('starts_at')
                    ->label('Début')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Fin'),
                Select::make('status')
                    ->label('Statut')
                    ->options(SubscriptionsTable::STATUSES)
                    ->default('active')
                    ->required(),
            ]);
    }
}
