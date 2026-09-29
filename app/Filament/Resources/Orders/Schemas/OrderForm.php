<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\Tables\OrdersTable;
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
                    ->label('N° de commande')
                    ->required(),
                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('prestataire_id')
                    ->label('Prestataire')
                    ->relationship('prestataire', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('service_id')
                    ->label('Service')
                    ->relationship('service', 'title')
                    ->searchable(),
                Select::make('service_request_id')
                    ->label('Demande de service')
                    ->relationship('serviceRequest', 'title')
                    ->searchable(),
                Textarea::make('requirements')
                    ->label('Besoins du client')
                    ->columnSpanFull(),
                // Montants et statuts verrouillés (même traitement que wallet_balance sur
                // UserForm et l'ensemble de PaymentForm) : un changement à la main ici ne
                // déclenche AUCUNE des actions réelles qui s'y rattachent normalement (webhook
                // FedaPay, escrow, notifications, crédit du portefeuille) — et prestataire_amount
                // en particulier est relu tel quel au moment d'un futur releasePayment()/
                // partialRefund(), donc une modification après coup fausserait un paiement réel
                // à venir. Les changements légitimes passent par les actions dédiées de la liste
                // (accepter, annuler, livrer, résoudre un litige, confirmer un remboursement...).
                TextInput::make('amount')
                    ->label('Montant')
                    ->required()
                    ->numeric()
                    ->suffix('FCFA')
                    ->disabled(),
                TextInput::make('client_fee')
                    ->label('Frais de service client')
                    ->numeric()
                    ->default(0)
                    ->suffix('FCFA')
                    ->disabled(),
                TextInput::make('commission')
                    ->label('Commission prestataire')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('FCFA')
                    ->disabled(),
                TextInput::make('prestataire_amount')
                    ->label('Net prestataire')
                    ->required()
                    ->numeric()
                    ->suffix('FCFA')
                    ->disabled(),
                TextInput::make('delivery_time')
                    ->label('Délai de livraison')
                    ->required()
                    ->numeric()
                    ->suffix('jours'),
                DateTimePicker::make('expected_delivery_at')
                    ->label('Livraison prévue'),
                DateTimePicker::make('delivered_at')
                    ->label('Livrée le'),
                Select::make('status')
                    ->label('Statut')
                    ->options(OrdersTable::STATUSES)
                    ->default('pending_payment')
                    ->required()
                    ->disabled(),
                Select::make('payment_status')
                    ->label('Paiement')
                    ->options(OrdersTable::PAYMENT_STATUSES)
                    ->default('pending')
                    ->required()
                    ->disabled(),
                Textarea::make('delivery_note')
                    ->label('Note de livraison')
                    ->columnSpanFull(),
                DateTimePicker::make('validation_deadline')
                    ->label('Validation avant le'),
                DateTimePicker::make('validated_at')
                    ->label('Validée le'),
                Toggle::make('auto_validated')
                    ->label('Validée automatiquement'),
            ]);
    }
}
