<?php

namespace App\Filament\Resources\News;

use App\Enums\ContentStatus;
use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Forms\SeoPreview;
use App\Filament\Resources\News\Pages\ManageNews;
use App\Models\News;
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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use UnitEnum;

class NewsResource extends Resource
{
    use HasPublicationActions;

    protected static ?string $model = News::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Actualités';

    protected static ?string $modelLabel = 'actualité';

    protected static ?string $pluralModelLabel = 'actualités';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $seoPreviews = SeoPreview::components('actualites');

        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        Section::make('Rédaction de l’article')
                            ->description('Rédigez un titre clair, un résumé court et le contenu complet de l’actualité.')
                            ->icon(Heroicon::OutlinedPencilSquare)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre de l’actualité')
                                    ->placeholder('Ex. Ouverture des inscriptions 2026-2027')
                                    ->required()
                                    ->maxLength(180)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('slug', Str::slug($state ?? ''))),
                                TextInput::make('slug')
                                    ->label('Adresse de la page')
                                    ->prefix('/actualites/')
                                    ->helperText('Générée automatiquement depuis le titre ; modifiez-la seulement si nécessaire.')
                                    ->required()
                                    ->rules([new PublicSlug])
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(180),
                                Textarea::make('excerpt')
                                    ->label('Résumé')
                                    ->placeholder('Présentez l’information essentielle en deux ou trois phrases.')
                                    ->helperText('Ce résumé apparaît dans les listes d’actualités et les résultats de recherche.')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(1000),
                                RichEditor::make('content')
                                    ->label('Contenu de l’article')
                                    ->helperText('Structurez le texte avec des sous-titres, listes et liens pour faciliter la lecture.')
                                    ->required()
                                    ->extraInputAttributes(['style' => 'min-height:28rem'])
                                    ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(['default' => 12, 'xl' => 8]),
                        Grid::make(1)
                            ->schema([
                                Section::make('Publication')
                                    ->description('Contrôlez la visibilité et la date de mise en ligne.')
                                    ->icon(Heroicon::OutlinedCalendarDays)
                                    ->schema([
                                        Select::make('status')
                                            ->label('Statut')
                                            ->options(fn (): array => static::publicationStatusOptions())
                                            ->disabled(fn (?News $record): bool => $record !== null && ! static::canManagePublicationStatus())
                                            ->default('draft')
                                            ->required()
                                            ->live(),
                                        DateTimePicker::make('published_at')
                                            ->label('Date de publication')
                                            ->helperText(fn (Get $get): string => $get->string('status') === 'scheduled'
                                                ? 'La publication sera automatiquement mise en ligne à cette date.'
                                                : 'Date affichée aux visiteurs pour cette actualité.')
                                            ->disabled(fn (?News $record): bool => $record !== null && ! static::canManagePublicationStatus())
                                            ->required(fn (Get $get): bool => $get->string('status') === 'scheduled')
                                            ->seconds(false),
                                        Toggle::make('is_featured')
                                            ->label('Mettre cette actualité à la une')
                                            ->helperText('Elle bénéficiera d’une mise en avant sur le site public.'),
                                    ]),
                                Section::make('Classement et illustration')
                                    ->description('Aidez les visiteurs à identifier rapidement l’article.')
                                    ->icon(Heroicon::OutlinedTag)
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Catégorie')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Select::make('author_id')
                                            ->label('Auteur')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Select::make('featured_image_id')
                                            ->label('Image principale')
                                            ->helperText('Choisissez une image horizontale depuis la médiathèque.')
                                            ->relationship(
                                                name: 'featuredImage',
                                                titleAttribute: 'original_name',
                                                modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                            )
                                            ->searchable()
                                            ->preload(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 12, 'xl' => 4]),
                        Section::make('Référencement et partage social')
                            ->description('Personnalisez l’apparence de l’article dans Google et sur les réseaux sociaux.')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->schema([
                                Tabs::make('Référencement')
                                    ->tabs([
                                        Tab::make('Google et moteurs de recherche')
                                            ->icon(Heroicon::OutlinedGlobeAlt)
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextInput::make('meta_title')
                                                        ->label('Titre SEO')
                                                        ->helperText('Idéalement entre 45 et 60 caractères.')
                                                        ->maxLength(70),
                                                    TextInput::make('canonical_url')
                                                        ->label('URL canonique')
                                                        ->placeholder('Laissez vide pour utiliser l’adresse de l’article')
                                                        ->rules([new SafeUrl]),
                                                ]),
                                                Textarea::make('meta_description')
                                                    ->label('Description SEO')
                                                    ->helperText('Idéalement entre 120 et 160 caractères.')
                                                    ->rows(3)
                                                    ->maxLength(180),
                                                TextInput::make('meta_keywords')
                                                    ->label('Mots-clés')
                                                    ->helperText('Séparez les expressions par des virgules.'),
                                                Grid::make(2)->schema([
                                                    Toggle::make('robots_index')
                                                        ->label('Autoriser l’indexation')
                                                        ->default(true),
                                                    Toggle::make('robots_follow')
                                                        ->label('Autoriser le suivi des liens')
                                                        ->default(true),
                                                ]),
                                                $seoPreviews[0],
                                            ]),
                                        Tab::make('Partage sur les réseaux sociaux')
                                            ->icon(Heroicon::OutlinedShare)
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    TextInput::make('og_title')
                                                        ->label('Titre du partage')
                                                        ->helperText('Laissez vide pour reprendre le titre SEO.')
                                                        ->maxLength(95),
                                                    Select::make('og_image_id')
                                                        ->label('Image du partage')
                                                        ->helperText('Format recommandé : 1200 × 630 px.')
                                                        ->relationship(
                                                            name: 'ogImage',
                                                            titleAttribute: 'original_name',
                                                            modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                                        )
                                                        ->searchable()
                                                        ->preload(),
                                                ]),
                                                Textarea::make('og_description')
                                                    ->label('Description du partage')
                                                    ->helperText('Laissez vide pour reprendre la description SEO.')
                                                    ->rows(3)
                                                    ->maxLength(200),
                                                $seoPreviews[1],
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull(),
                        Section::make('Documents et galeries liés')
                            ->description('Ajoutez, si nécessaire, des ressources complémentaires à l’article.')
                            ->icon(Heroicon::OutlinedPaperClip)
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('documents')
                                        ->label('Documents')
                                        ->relationship('documents', 'title')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                    Select::make('galleries')
                                        ->label('Galeries')
                                        ->relationship('galleries', 'title')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('featuredImage.path')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->size(48),
                TextColumn::make('title')
                    ->label('Titre')
                    ->description(fn (News $record): string => Str::limit($record->excerpt, 85))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => ($state instanceof ContentStatus ? $state : ContentStatus::tryFrom((string) $state))?->label() ?? (string) $state)
                    ->color(fn (mixed $state): string => match ($state instanceof ContentStatus ? $state->value : (string) $state) {
                        'published' => 'success',
                        'scheduled' => 'info',
                        'pending' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('À la une')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Vues')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
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
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                ...static::publicationActions(),
                EditAction::make()
                    ->modalWidth(Width::ScreenExtraLarge)
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalDescription('Modifiez le contenu, la publication et le référencement de l’actualité.'),
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
            'index' => ManageNews::route('/'),
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
