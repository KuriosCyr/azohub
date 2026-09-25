<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Signalements';
    protected static ?string $modelLabel = 'Signalement';
    protected static ?string $pluralModelLabel = 'Signalements';
    protected static \UnitEnum|string|null $navigationGroup = 'Modération';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du signalement')
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'title')
                            ->required()
                            ->searchable()
                            ->disabled(),

                        Forms\Components\Select::make('reporter_id')
                            ->label('Signalé par')
                            ->relationship('reporter', 'name')
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('reason')
                            ->label('Raison')
                            ->options(Report::reasonLabels())
                            ->required()
                            ->disabled(),

                        Forms\Components\Textarea::make('details')
                            ->label('Détails')
                            ->rows(3)
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(Report::statusLabels())
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),

                Section::make('Traitement par l\'admin')
                    ->schema([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes de l\'administrateur')
                            ->rows(4)
                            ->placeholder('Détails de votre décision...'),

                        Forms\Components\Select::make('reviewed_by')
                            ->label('Examiné par')
                            ->relationship('reviewer', 'name')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Examiné le')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.title')
                    ->label('Service')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->url(fn (Report $record) => route('services.show', $record->service->slug), true),

                Tables\Columns\TextColumn::make('service.prestataire.name')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Signalé par')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('reason')
                    ->label('Raison')
                    ->formatStateUsing(fn (string $state): string => Report::reasonLabels()[$state] ?? $state)
                    ->colors([
                        'danger' => ['inappropriate_content', 'scam'],
                        'warning' => ['misleading_info', 'poor_quality', 'spam'],
                        'info' => ['copyright_violation', 'other'],
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => Report::statusLabels()[$state] ?? $state)
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'reviewing',
                        'success' => 'resolved',
                        'gray' => 'dismissed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Signalé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Examiné le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                // Pas de valeur par défaut : le badge de navigation compte "En attente" +
                // "En cours d'examen" ensemble (voir getNavigationBadge() ci-dessous) — un filtre
                // par défaut sur "En attente" seul masquait silencieusement les signalements "En
                // cours d'examen" de cette liste tout en les comptant dans le badge, donnant
                // l'impression qu'un signalement annoncé par le badge avait disparu.
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Report::statusLabels()),

                Tables\Filters\SelectFilter::make('reason')
                    ->label('Raison')
                    ->options(Report::reasonLabels()),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                
                Action::make('mark_reviewing')
                    ->label('Examiner')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Report $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Report $record, $livewire) {
                        $record->update([
                            'status' => 'reviewing',
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Signalement marqué « En cours d\'examen »')
                            ->success()
                            ->send();

                        // Le badge "Signalements" de la sidebar (voir getNavigationBadge()) n'est
                        // recalculé qu'au chargement complet d'une page : sans cet événement, il
                        // reste affiché tel quel jusqu'à ce que l'admin recharge manuellement,
                        // même quand l'action vient de changer le nombre qu'il est censé montrer.
                        $livewire->dispatch('refresh-sidebar');
                    }),

                Action::make('resolve')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Report $record) => in_array($record->status, ['pending', 'reviewing']))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes de résolution')
                            ->required()
                            ->placeholder('Expliquez les actions prises...'),
                        
                        Forms\Components\Toggle::make('disable_service')
                            ->label('Désactiver le service signalé')
                            ->default(false),
                    ])
                    ->action(function (Report $record, array $data, $livewire) {
                        $record->update([
                            'status' => 'resolved',
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        // Désactiver le service si demandé
                        if ($data['disable_service']) {
                            $record->service->update(['is_active' => false]);
                        }

                        Notification::make()
                            ->title('Signalement résolu')
                            ->success()
                            ->send();

                        $livewire->dispatch('refresh-sidebar');
                    }),

                Action::make('dismiss')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Report $record) => in_array($record->status, ['pending', 'reviewing']))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Raison du rejet')
                            ->required()
                            ->placeholder('Expliquez pourquoi ce signalement n\'est pas fondé...'),
                    ])
                    ->action(function (Report $record, array $data, $livewire) {
                        $record->update([
                            'status' => 'dismissed',
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Signalement rejeté')
                            ->success()
                            ->send();

                        $livewire->dispatch('refresh-sidebar');
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('mark_reviewing')
                        ->label('Marquer en examen')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records, $livewire) {
                            $records->each->update([
                                'status' => 'reviewing',
                                'reviewed_by' => Auth::id(),
                                'reviewed_at' => now(),
                            ]);

                            Notification::make()
                                ->title($records->count() . ' signalement(s) marqué(s) « En cours d\'examen »')
                                ->success()
                                ->send();

                            $livewire->dispatch('refresh-sidebar');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'reviewing'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        
        if ($count > 10) {
            return 'danger';
        } elseif ($count > 5) {
            return 'warning';
        }
        
        return 'primary';
    }
}