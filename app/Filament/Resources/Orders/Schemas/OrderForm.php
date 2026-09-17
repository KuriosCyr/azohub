<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                TextInput::make('prestataire_id')
                    ->required()
                    ->numeric(),
                TextInput::make('service_id')
                    ->numeric(),
                TextInput::make('service_request_id')
                    ->numeric(),
                TextInput::make('proposal_id')
                    ->numeric(),
                Textarea::make('requirements')
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('commission')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('prestataire_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('delivery_time')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('expected_delivery_at'),
                DateTimePicker::make('delivered_at'),
                Select::make('status')
                    ->options([
            'pending_payment' => 'Pending payment',
            'paid' => 'Paid',
            'in_progress' => 'In progress',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'disputed' => 'Disputed',
        ])
                    ->default('pending_payment')
                    ->required(),
                Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'held' => 'Held',
                        'released' => 'Released',
                        'refund_pending' => 'Refund pending',
                        'refunded' => 'Refunded',
                    ])
                    ->default('pending')
                    ->required(),
                TextInput::make('deliverables'),
                Textarea::make('delivery_note')
                    ->columnSpanFull(),
                DateTimePicker::make('validation_deadline'),
                DateTimePicker::make('validated_at'),
                Toggle::make('auto_validated')
                    ->required(),
            ]);
    }
}
