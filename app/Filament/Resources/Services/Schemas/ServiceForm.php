<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\Service;
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
                Select::make('user_id')
                    ->label('Prestataire')
                    ->relationship('prestataire', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->label('Titre')
                    ->required(),
                TextInput::make('slug')
                    ->label('Lien (slug)')
                    ->required(),
                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('what_included')
                    ->label('Ce qui est inclus')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Prix')
                    ->required()
                    ->numeric()
                    ->suffix('FCFA'),
                Select::make('price_type')
                    ->label('Type de prix')
                    ->options(['fixe' => 'Prix fixe', 'a_partir_de' => 'À partir de'])
                    ->default('fixe')
                    ->required(),
                TextInput::make('delivery_time')
                    ->label('Délai de livraison')
                    ->required()
                    ->numeric()
                    ->suffix('jours'),
                TextInput::make('city')
                    ->label('Ville'),
                Toggle::make('serves_nationwide')
                    ->label('Intervient partout au Bénin'),
                Select::make('service_areas')
                    ->label("Communes d'intervention")
                    ->multiple()
                    ->searchable()
                    ->options(fn () => array_combine(Service::communes(), Service::communes()))
                    ->helperText('Ignorées si « partout au Bénin » est activé.')
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->label('Image de couverture')
                    ->image()
                    ->disk('public')
                    ->directory('services/covers'),
                TextInput::make('rating')
                    ->label('Note moyenne')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_orders')
                    ->label('Commandes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_reviews')
                    ->label('Avis')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->label('Modération')
                    ->options(Service::STATUS_LABELS)
                    ->default('pending')
                    ->required()
                    ->helperText('Pour approuver ou refuser avec un motif (et prévenir le prestataire), utilisez plutôt les boutons de la liste.'),
                Textarea::make('moderation_note')
                    ->label('Motif du refus')
                    ->helperText('Visible par le prestataire.')
                    ->columnSpanFull(),
                Toggle::make('is_featured')
                    ->label('Sponsorisé (mis en avant)'),
            ]);
    }
}
