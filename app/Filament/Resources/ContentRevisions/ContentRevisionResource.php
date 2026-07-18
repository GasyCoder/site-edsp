<?php

namespace App\Filament\Resources\ContentRevisions;

use App\Filament\Resources\ContentRevisions\Pages\ManageContentRevisions;
use App\Models\ContentRevision;
use App\Services\ContentRevisionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ContentRevisionResource extends Resource
{
    protected static ?string $model = ContentRevision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Gouvernance';

    protected static ?string $navigationLabel = 'Historique des contenus';

    protected static ?string $modelLabel = 'révision';

    protected static ?string $recordTitleAttribute = 'action';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Révision')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('action')
                                ->label('Action')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('created_at')
                                ->label('Date')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('revisionable_type')
                                ->label('Type de contenu')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('revisionable_id')
                                ->label('Identifiant')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                        KeyValue::make('old_values')
                            ->label('Anciennes valeurs')
                            ->disabled()
                            ->dehydrated(false),
                        KeyValue::make('new_values')
                            ->label('Nouvelles valeurs')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('comparison_hint')
                            ->label('Comparaison')
                            ->default('Utilisez les deux blocs ci-dessus pour comparer les champs modifiés.')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->searchable(),
                TextColumn::make('revisionable_type')
                    ->label('Contenu')
                    ->formatStateUsing(fn (?string $state): string => Str::afterLast($state ?? '', '\\'))
                    ->sortable(),
                TextColumn::make('revisionable_id')
                    ->label('ID')
                    ->numeric(),
                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->default('Système'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'created' => 'Création',
                        'updated' => 'Modification',
                        'restored' => 'Restauration',
                        'published' => 'Publication',
                        'archived' => 'Archivage',
                        'deleted' => 'Suppression',
                        'force_deleted' => 'Suppression définitive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Comparer')
                    ->icon(Heroicon::OutlinedEye)
                    ->tooltip('Comparer'),
                Action::make('restore')
                    ->label('Restaurer cette version')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->iconButton()
                    ->tooltip('Restaurer cette version')
                    ->authorize('restore')
                    ->requiresConfirmation()
                    ->visible(fn (ContentRevision $record): bool => $record->revisionable !== null && filled($record->old_values))
                    ->action(function (ContentRevision $record): void {
                        app(ContentRevisionService::class)->restore($record, auth()->id());

                        Notification::make()
                            ->title('Version restaurée')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContentRevisions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
