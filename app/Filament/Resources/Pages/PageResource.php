<?php

namespace App\Filament\Resources\Pages;

use App\Enums\ContentStatus;
use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Forms\SeoPreview;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Models\Page;
use App\Rules\PublicSlug;
use App\Rules\SafeUrl;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class PageResource extends Resource
{
    use HasPublicationActions;

    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'page';

    protected static ?string $pluralModelLabel = 'pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contenu')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(180)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('slug', Str::slug($state ?? ''))),
                            TextInput::make('slug')
                                ->required()
                                ->rules([new PublicSlug])
                                ->unique(ignoreRecord: true)
                                ->maxLength(180),
                            Select::make('status')
                                ->label('Statut')
                                ->options(fn (): array => static::publicationStatusOptions())
                                ->disabled(fn (?Page $record): bool => $record !== null && ! static::canManagePublicationStatus())
                                ->default('draft')
                                ->required(),
                            TextInput::make('template')
                                ->required()
                                ->default('default'),
                            TextInput::make('published_at')
                                ->label('Publication')
                                ->type('datetime-local')
                                ->disabled(fn (?Page $record): bool => $record !== null && ! static::canManagePublicationStatus())
                                ->required(fn (Get $get): bool => $get->string('status') === 'scheduled'),
                        ]),
                    ]),
                Section::make('Référencement')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Titre SEO')
                            ->maxLength(70),
                        Textarea::make('meta_description')
                            ->label('Description SEO')
                            ->rows(3)
                            ->maxLength(180),
                        Grid::make(2)->schema([
                            TextInput::make('canonical_url')
                                ->label('URL canonique')
                                ->rules([new SafeUrl]),
                            TextInput::make('meta_keywords')
                                ->label('Mots-clés (facultatif)'),
                            Toggle::make('robots_index')
                                ->label('Autoriser l’indexation')
                                ->default(true),
                            Toggle::make('robots_follow')
                                ->label('Suivre les liens')
                                ->default(true),
                            TextInput::make('og_title')
                                ->label('Titre Open Graph'),
                            Textarea::make('og_description')
                                ->label('Description Open Graph')
                                ->rows(2),
                            Select::make('og_image_id')
                                ->label('Image Open Graph')
                                ->relationship(
                                    name: 'ogImage',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->searchable()
                                ->preload(),
                        ]),
                        ...SeoPreview::components(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => ($state instanceof ContentStatus ? $state : ContentStatus::tryFrom((string) $state))?->label() ?? (string) $state)
                    ->sortable(),
                IconColumn::make('robots_index')
                    ->label('Indexée')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modifiée')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Brouillon',
                        'pending' => 'En attente',
                        'scheduled' => 'Programmé',
                        'published' => 'Publié',
                        'archived' => 'Archivé',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                ...static::publicationActions(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePages::route('/'),
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
