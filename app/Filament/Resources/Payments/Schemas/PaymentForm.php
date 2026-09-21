<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Filament\Resources\Payments\Tables\PaymentsTable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

// Lecture seule : un paiement n'est jamais créé ni modifié à la main. Sa confirmation passe par
// FedaPay (webhook / retour de paiement) qui déclenche aussi la commande, l'escrow et les
// notifications ; changer un statut ici contournerait tout cela.
class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->disabled()
            ->components([
                TextInput::make('transaction_id')
                    ->label('N° de transaction'),
                Select::make('order_id')
                    ->label('Commande')
                    ->relationship('order', 'order_number'),
                Select::make('user_id')
                    ->label('Payeur')
                    ->relationship('user', 'name'),
                TextInput::make('amount')
                    ->label('Montant')
                    ->suffix('FCFA'),
                Select::make('payment_method')
                    ->label('Moyen de paiement')
                    ->options(PaymentsTable::METHODS),
                TextInput::make('phone_number')
                    ->label('Téléphone'),
                Select::make('status')
                    ->label('Statut')
                    ->options(PaymentsTable::STATUSES),
                Select::make('type')
                    ->label('Type')
                    ->options(PaymentsTable::TYPES),
                Textarea::make('gateway_response')
                    ->label('Réponse FedaPay')
                    ->columnSpanFull(),
                TextInput::make('gateway_reference')
                    ->label('Référence FedaPay'),
                DateTimePicker::make('paid_at')
                    ->label('Payé le'),
            ]);
    }
}
