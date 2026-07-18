<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Resources\Galleries\Pages\ManageGalleries;
use App\Models\Gallery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Médiathèque';

    protected static ?string $navigationLabel = 'Galeries';

    protected static ?string $modelLabel = 'galerie';

    protected static ?string $pluralModelLabel = 'galeries';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Présentation')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('slug', Str::slug($state ?? ''))),
                            TextInput::make('slug')
                                ->label('Identifiant URL')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Select::make('cover_image_id')
                                ->label('Image de couverture')
                                ->relationship(
                                    name: 'coverImage',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->searchable()
                                ->preload(),
                            TextInput::make('position')
                                ->label('Ordre d’affichage')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ]),
                Section::make('Images de la galerie')
                    ->description('Choisissez des images déjà téléversées dans la médiathèque et organisez-les par glisser-déposer.')
                    ->schema([
                        Repeater::make('images')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('position')
                            ->schema([
                                Select::make('media_id')
                                    ->label('Image')
                                    ->relationship(
                                        name: 'media',
                                        titleAttribute: 'original_name',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->required(),
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->maxLength(255),
                                TextInput::make('alt_text')
                                    ->label('Texte alternatif')
                                    ->maxLength(255),
                                Textarea::make('caption')
                                    ->label('Légende')
                                    ->rows(2)
                                    ->maxLength(1000),
                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): string => filled($state['title'] ?? null) ? $state['title'] : 'Image')
                            ->itemNumbers()
                            ->collapsible()
                            ->collapsed()
                            ->addActionLabel('Ajouter une image')
                            ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
                            ->columnSpanFull(),
                    ]),
                Section::make('Publication')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options(self::statusOptions())
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->seconds(false),
                        Toggle::make('is_visible')
                            ->label('Afficher sur le site')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('position')
            ->columns([
                ImageColumn::make('coverImage.path')
                    ->label('Couverture')
                    ->disk('public')
                    ->square()
                    ->size(54),
                TextColumn::make('title')
                    ->label('Galerie')
                    ->description(fn (Gallery $record): ?string => filled($record->description) ? Str::limit($record->description, 65) : null)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('images_count')
                    ->label('Images')
                    ->counts('images')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::statusOptions()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non planifiée')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statusOptions()),
                TernaryFilter::make('is_visible')
                    ->label('Visibilité'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('7xl'),
                DeleteAction::make()
                    ->requiresConfirmation(),
                ForceDeleteAction::make()
                    ->requiresConfirmation(),
                RestoreAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    RestoreBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    /** @return array<string, string> */
    private static function statusOptions(): array
    {
        return [
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'archived' => 'Archivé',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGalleries::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
