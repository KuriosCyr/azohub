<?php

namespace App\Filament\Resources\Proposals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProposalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('service_request_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('proposed_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('delivery_time')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('accepted_at'),
            ]);
    }
}
