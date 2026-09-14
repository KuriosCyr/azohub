<?php

namespace App\Filament\Resources\Disputes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DisputeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                TextInput::make('opened_by')
                    ->required()
                    ->numeric(),
                Select::make('reason')
                    ->options([
            'work_not_delivered' => 'Work not delivered',
            'work_not_conform' => 'Work not conform',
            'poor_quality' => 'Poor quality',
            'late_delivery' => 'Late delivery',
            'payment_issue' => 'Payment issue',
            'other' => 'Other',
        ])
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('evidences'),
                Select::make('status')
                    ->options([
            'open' => 'Open',
            'under_review' => 'Under review',
            'resolved' => 'Resolved',
            'cancelled' => 'Cancelled',
        ])
                    ->default('open')
                    ->required(),
                Select::make('resolution')
                    ->options([
            'refund_client' => 'Refund client',
            'pay_prestataire' => 'Pay prestataire',
            'partial_refund' => 'Partial refund',
            'no_action' => 'No action',
        ]),
                Textarea::make('admin_note')
                    ->columnSpanFull(),
                TextInput::make('resolved_by')
                    ->numeric(),
                DateTimePicker::make('resolved_at'),
            ]);
    }
}
