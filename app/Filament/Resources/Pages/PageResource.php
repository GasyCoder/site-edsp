<?php

namespace App\Filament\Resources\Pages;

use App\Enums\ContentStatus;
use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Forms\MediaImagePreview;
use App\Filament\Forms\SeoPreview;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Filament\Resources\PageSections\PageSectionResource;
use App\Models\Media;
use App\Models\Page;
use App\Rules\PublicSlug;
use App\Rules\SafeUrl;
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
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
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
use Mews\Purifier\Facades\Purifier;
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
                    ->description('Identité de la page, adresse publique et état de publication.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Titre')
                                ->required()
                                ->maxLength(180)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('slug', Str::slug($state ?? ''))),
                            TextInput::make('slug')
                                ->label('Adresse de la page')
                                ->prefix('/')
                                ->helperText('Cette adresse est utilisée dans le menu et les liens publics.')
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
                Section::make('Mot du directeur')
                    ->description('Ce contenu est affiché directement sur la page publique /presentation.')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->relationship('mainSection')
                    ->visible(fn (?Page $record): bool => $record?->slug === 'presentation')
                    ->schema([
                        Hidden::make('section_key')->default('main'),
                        Hidden::make('section_type')->default('director-message'),
                        Hidden::make('position')->default(1),
                        Hidden::make('is_visible')->default(true),
                        Hidden::make('settings.background')->default('white'),
                        Hidden::make('settings.alignment')->default('left'),
                        Hidden::make('settings.container')->default('wide'),
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Nom et titre académique')
                                ->placeholder('Pr. NOM Prénom')
                                ->required()
                                ->maxLength(180),
                            TextInput::make('subtitle')
                                ->label('Libellé de la section')
                                ->placeholder('Mot du directeur')
                                ->required()
                                ->maxLength(255),
                        ]),
                        RichEditor::make('content')
                            ->label('Message complet')
                            ->helperText('Les paragraphes saisis ici sont affichés immédiatement sur /presentation après enregistrement.')
                            ->required()
                            ->extraInputAttributes(['style' => 'min-height:24rem'])
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            Select::make('image_id')
                                ->label('Portrait officiel')
                                ->helperText('Choisissez une image déjà importée dans la médiathèque.')
                                ->relationship(
                                    name: 'image',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaImagePreview::optionLabel($record))
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('image_preview')
                                ->label('Aperçu du portrait')
                                ->content(fn (Get $get) => MediaImagePreview::render($get->integer('image_id'), 'Aucun portrait sélectionné.')),
                            TextInput::make('settings.alt_text')
                                ->label('Description accessible du portrait')
                                ->placeholder('Portrait du directeur de l’EDSP')
                                ->maxLength(255),
                            TextInput::make('settings.director_position')
                                ->label('Fonction officielle')
                                ->placeholder('Directeur de l’EDSP')
                                ->required()
                                ->maxLength(180),
                            TextInput::make('settings.director_signature')
                                ->label('Formule de clôture')
                                ->placeholder('Avec tous mes encouragements,')
                                ->maxLength(255),
                        ]),
                        Section::make('Traduction anglaise du message')
                            ->description('Ces champs sont affichés lorsque le visiteur sélectionne English.')
                            ->icon(Heroicon::OutlinedLanguage)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('translations.en.title')->label("Director's name and title")->maxLength(180),
                                    TextInput::make('translations.en.subtitle')->label('Section label')->maxLength(255),
                                ]),
                                RichEditor::make('translations.en.content')
                                    ->label("Director's full message")
                                    ->extraInputAttributes(['style' => 'min-height:20rem'])
                                    ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                                    ->columnSpanFull(),
                                Grid::make(2)->schema([
                                    TextInput::make('translations.en.settings.director_position')->label('Official position')->maxLength(180),
                                    TextInput::make('translations.en.settings.director_signature')->label('Closing line')->maxLength(255),
                                    TextInput::make('translations.en.settings.alt_text')->label('Portrait alternative text')->maxLength(255),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ]),
                Section::make('Référencement')
                    ->description('Contrôlez la présentation de cette page dans Google et lors du partage sur les réseaux sociaux.')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
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
                                ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaImagePreview::optionLabel($record))
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('og_image_preview')
                                ->label('Aperçu Open Graph')
                                ->content(fn (Get $get) => MediaImagePreview::render($get->integer('og_image_id'), 'Aucune image Open Graph sélectionnée.')),
                        ]),
                        ...SeoPreview::components(),
                    ])
                    ->collapsible(),
                Section::make('Version anglaise')
                    ->description('Contenu affiché lorsque le visiteur choisit English. Un champ vide utilise temporairement la version française.')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->schema([
                        TextInput::make('translations.en.title')->label('Title')->maxLength(180),
                        TextInput::make('translations.en.meta_title')->label('SEO title')->maxLength(70),
                        Textarea::make('translations.en.meta_description')->label('SEO description')->rows(3)->maxLength(180),
                        TextInput::make('translations.en.meta_keywords')->label('Keywords'),
                        TextInput::make('translations.en.og_title')->label('Open Graph title')->maxLength(95),
                        Textarea::make('translations.en.og_description')->label('Open Graph description')->rows(2)->maxLength(200),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
                TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Blocs')
                    ->badge()
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
                ViewAction::make()->modalWidth('7xl'),
                Action::make('manageContent')
                    ->label('Contenu')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->iconButton()
                    ->tooltip('Gérer le contenu et les sections de cette page')
                    ->url(fn (Page $record): string => PageSectionResource::getUrl('index', [
                        'tableFilters' => ['page_id' => ['value' => $record->getKey()]],
                    ])),
                ...static::publicationActions(),
                EditAction::make()
                    ->modalHeading('Paramètres de la page')
                    ->modalDescription('Modifiez l’identité, la publication, le référencement et la traduction anglaise.')
                    ->modalWidth('7xl'),
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
