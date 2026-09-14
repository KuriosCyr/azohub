<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transaction_id')
                    ->required(),
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('payment_method')
                    ->options([
            'mtn_momo' => 'Mtn momo',
            'moov_money' => 'Moov money',
            'celtiis_cash' => 'Celtiis cash',
            'card' => 'Card',
        ])
                    ->default('mtn_momo')
                    ->required(),
                TextInput::make('phone_number')
                    ->tel(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'success' => 'Success', 'failed' => 'Failed', 'refunded' => 'Refunded'])
                    ->default('pending')
                    ->required(),
                Select::make('type')
                    ->options([
            'order_payment' => 'Order payment',
            'subscription' => 'Subscription',
            'withdrawal' => 'Withdrawal',
        ])
                    ->default('order_payment')
                    ->required(),
                Textarea::make('gateway_response')
                    ->columnSpanFull(),
                TextInput::make('gateway_reference'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
