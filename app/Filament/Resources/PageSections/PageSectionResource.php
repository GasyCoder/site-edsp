<?php

namespace App\Filament\Resources\PageSections;

use App\Filament\Resources\PageSections\Pages\ManagePageSections;
use App\Models\Media;
use App\Models\PageSection;
use App\Rules\SafeUrl;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;
use UnitEnum;

class PageSectionResource extends Resource
{
    protected static ?string $model = PageSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Contenu des pages';

    protected static ?string $modelLabel = 'section';

    protected static ?string $recordTitleAttribute = 'section_key';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organisation de la section')
                    ->description('Choisissez la page, le type de rendu et la position de ce bloc.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('page_id')
                                ->label('Page')
                                ->relationship('page', 'title')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('section_key')
                                ->label('Clé technique')
                                ->required()
                                ->rules(fn (?PageSection $record, callable $get): array => [
                                    Rule::unique('page_sections', 'section_key')
                                        ->where('page_id', $get('page_id'))
                                        ->ignore($record?->getKey()),
                                ])
                                ->maxLength(100),
                            Select::make('section_type')
                                ->label('Type')
                                ->options([
                                    'hero' => 'Hero',
                                    'content' => 'Contenu',
                                    'rich-content' => 'Contenu éditorial',
                                    'director-message' => 'Mot du directeur',
                                    'presentation' => 'Présentation — accueil',
                                    'features' => 'Cartes',
                                    'programs' => 'Formations',
                                    'stats' => 'Chiffres clés',
                                    'admissions' => 'Admissions',
                                    'news' => 'Actualités',
                                    'student_life' => 'Vie étudiante',
                                    'library' => 'Bibliothèque',
                                    'team' => 'Équipe',
                                    'testimonials' => 'Témoignages',
                                    'cta' => 'Appel à l’action',
                                ])
                                ->live()
                                ->required(),
                            TextInput::make('position')
                                ->label('Ordre')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                            Toggle::make('is_visible')
                                ->label('Afficher sur le site')
                                ->default(true),
                        ]),
                    ]),
                Section::make('Contenu principal')
                    ->description(fn (Get $get): string => $get->string('section_type') === 'director-message'
                        ? 'Renseignez l’identité, le message officiel et le portrait affichés sur la page publique.'
                        : 'Renseignez les textes et le média principal de cette section.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextInput::make('title')
                            ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? 'Nom et titre académique du directeur' : 'Titre')
                            ->placeholder(fn (Get $get): ?string => $get->string('section_type') === 'director-message' ? 'Pr. NOM Prénom' : null)
                            ->maxLength(180),
                        TextInput::make('subtitle')
                            ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? 'Libellé au-dessus du nom' : 'Sur-titre / sous-titre')
                            ->placeholder(fn (Get $get): ?string => $get->string('section_type') === 'director-message' ? 'Mot du directeur' : null)
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? 'Message complet du directeur' : 'Contenu')
                            ->helperText(fn (Get $get): string => $get->string('section_type') === 'director-message'
                                ? 'Séparez le message en paragraphes courts pour assurer une lecture confortable sur mobile.'
                                : 'Utilisez des paragraphes, sous-titres et listes pour structurer le contenu.')
                            ->extraInputAttributes(['style' => 'min-height:22rem'])
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            Select::make('image_id')
                                ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? 'Portrait officiel' : 'Média principal')
                                ->helperText('Sélectionnez une image déjà importée dans la médiathèque. Son aperçu apparaît immédiatement à droite.')
                                ->relationship(
                                    name: 'image',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->getOptionLabelFromRecordUsing(fn (Media $record): string => static::mediaOptionLabel($record))
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('image_preview')
                                ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? 'Aperçu du portrait' : 'Aperçu du média')
                                ->content(fn (Get $get): HtmlString => static::mediaPreview(
                                    $get->integer('image_id'),
                                    $get('settings.image_zoom'),
                                    $get('settings.image_position_x'),
                                    $get('settings.image_position_y'),
                                )),
                            TextInput::make('button_text')
                                ->label('Texte du bouton')
                                ->maxLength(80),
                            TextInput::make('button_url')
                                ->label('Lien du bouton')
                                ->rules([new SafeUrl])
                                ->maxLength(2048),
                        ]),
                    ]),
                Section::make('Identité et signature')
                    ->description('Ces informations accompagnent le portrait et la signature du message.')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->visible(fn (Get $get): bool => $get->string('section_type') === 'director-message')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('settings.director_position')
                                ->label('Fonction officielle')
                                ->placeholder('Directeur de l’EDSP')
                                ->maxLength(180),
                            TextInput::make('settings.director_signature')
                                ->label('Formule de clôture')
                                ->placeholder('Avec tous mes encouragements,')
                                ->maxLength(255),
                        ]),
                        TextInput::make('settings.alt_text')
                            ->label('Description accessible du portrait')
                            ->helperText('Décrivez brièvement la photo pour les personnes utilisant un lecteur d’écran.')
                            ->placeholder('Portrait du directeur de l’EDSP')
                            ->maxLength(255),
                    ]),
                Section::make('Galerie de la vie étudiante')
                    ->description('Gérez les deux petits visuels affichés à droite de la photo principale sur la page d’accueil.')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->visible(fn (Get $get): bool => in_array($get->string('section_type'), ['student_life', 'student-life'], true))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('settings.secondary_media_id')
                                ->label('Photo conférence')
                                ->helperText('Visuel affiché dans le cadre supérieur droit.')
                                ->options(fn (): array => static::imageMediaOptions())
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('secondary_media_preview')
                                ->label('Aperçu — conférence')
                                ->content(fn (Get $get): HtmlString => static::mediaPreview($get->integer('settings.secondary_media_id'))),
                            Select::make('settings.tertiary_media_id')
                                ->label('Photo événement étudiant')
                                ->helperText('Visuel affiché dans le cadre inférieur droit.')
                                ->options(fn (): array => static::imageMediaOptions())
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('tertiary_media_preview')
                                ->label('Aperçu — événement étudiant')
                                ->content(fn (Get $get): HtmlString => static::mediaPreview($get->integer('settings.tertiary_media_id'))),
                        ]),
                    ]),
                Section::make('Cadrage du portrait')
                    ->description('Ajustez le portrait sans modifier le fichier original. L’aperçu se met à jour immédiatement au-dessus.')
                    ->icon(Heroicon::OutlinedMagnifyingGlassPlus)
                    ->visible(fn (Get $get): bool => $get->string('section_type') === 'director-message')
                    ->schema([
                        Slider::make('settings.image_zoom')
                            ->label('Zoom')
                            ->range(50, 200)
                            ->default(100)
                            ->step(1)
                            ->tooltips()
                            ->live(),
                        Grid::make(2)->schema([
                            Slider::make('settings.image_position_x')
                                ->label('Position horizontale')
                                ->range(0, 100)
                                ->default(50)
                                ->step(1)
                                ->tooltips()
                                ->live(),
                            Slider::make('settings.image_position_y')
                                ->label('Position verticale')
                                ->range(0, 100)
                                ->default(50)
                                ->step(1)
                                ->tooltips()
                                ->live(),
                        ]),
                        Actions::make([
                            Action::make('reset_portrait_crop')
                                ->label('Réinitialiser le cadrage')
                                ->icon(Heroicon::OutlinedArrowPath)
                                ->color('gray')
                                ->action(function (Set $set): void {
                                    $set('settings.image_zoom', 100, shouldCallUpdatedHooks: true);
                                    $set('settings.image_position_x', 50, shouldCallUpdatedHooks: true);
                                    $set('settings.image_position_y', 50, shouldCallUpdatedHooks: true);
                                }),
                        ]),
                    ]),
                Section::make('Paramètres contrôlés')
                    ->description('Réglages visuels du bloc sur le site public.')
                    ->icon(Heroicon::OutlinedSwatch)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('settings.background')
                                ->label('Arrière-plan')
                                ->options([
                                    'white' => 'Blanc',
                                    'light' => 'Clair',
                                    'blue' => 'Bleu',
                                ]),
                            Select::make('settings.alignment')
                                ->label('Alignement')
                                ->options([
                                    'left' => 'Gauche',
                                    'center' => 'Centre',
                                ]),
                            Select::make('settings.container')
                                ->label('Largeur')
                                ->options([
                                    'narrow' => 'Étroit',
                                    'default' => 'Standard',
                                    'wide' => 'Large',
                                ]),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
                Section::make('Version anglaise')
                    ->description('Traduisez le contenu visible de cette section. Les liens et médias restent communs aux deux langues.')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->schema([
                        TextInput::make('translations.en.title')
                            ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? "Director's name and academic title" : 'Title')
                            ->maxLength(180),
                        TextInput::make('translations.en.subtitle')->label('Eyebrow / subtitle')->maxLength(255),
                        RichEditor::make('translations.en.content')
                            ->label(fn (Get $get): string => $get->string('section_type') === 'director-message' ? "Director's full message" : 'Content')
                            ->extraInputAttributes(['style' => 'min-height:20rem'])
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                        TextInput::make('translations.en.button_text')->label('Button label')->maxLength(80),
                        Grid::make(2)
                            ->visible(fn (Get $get): bool => $get->string('section_type') === 'director-message')
                            ->schema([
                                TextInput::make('translations.en.settings.director_position')->label('Official position')->maxLength(180),
                                TextInput::make('translations.en.settings.director_signature')->label('Closing line')->maxLength(255),
                                TextInput::make('translations.en.settings.alt_text')->label('Portrait alternative text')->maxLength(255),
                            ]),
                        KeyValue::make('translations.en.settings')
                            ->label('Advanced English labels')
                            ->helperText('Optional: use the same technical keys as in the section settings for secondary labels.')
                            ->keyLabel('Technical key')
                            ->valueLabel('English text')
                            ->addActionLabel('Add a label')
                            ->reorderable(false)
                            ->hidden(fn (Get $get): bool => $get->string('section_type') === 'director-message')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('section_key')
            ->columns([
                TextColumn::make('section_key')
                    ->label('Clé')
                    ->searchable(),
                TextColumn::make('page.title')
                    ->label('Page')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('section_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('page_id')
                    ->label('Page')
                    ->relationship('page', 'title'),
                SelectFilter::make('section_type')
                    ->label('Type')
                    ->options([
                        'hero' => 'Hero',
                        'content' => 'Contenu',
                        'rich-content' => 'Contenu éditorial',
                        'director-message' => 'Mot du directeur',
                        'presentation' => 'Présentation — accueil',
                        'features' => 'Cartes',
                        'programs' => 'Formations',
                        'stats' => 'Chiffres clés',
                        'admissions' => 'Admissions',
                        'news' => 'Actualités',
                        'student_life' => 'Vie étudiante',
                        'library' => 'Bibliothèque',
                        'team' => 'Équipe',
                        'testimonials' => 'Témoignages',
                        'cta' => 'Appel à l’action',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Modifier la section')
                    ->modalDescription('Organisez le contenu, le média, les traductions et l’apparence depuis un seul écran.')
                    ->modalWidth('7xl')
                    ->mutateDataUsing(function (array $data, PageSection $record): array {
                        $data['settings'] = [
                            ...($record->settings ?? []),
                            ...($data['settings'] ?? []),
                        ];

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePageSections::route('/'),
        ];
    }

    private static function mediaOptionLabel(Media $media): string
    {
        $name = e($media->original_name);

        if (blank($media->thumbnail_url)) {
            return $name;
        }

        return '<span style="display:flex;align-items:center;gap:.75rem">'
            .'<img src="'.e($media->thumbnail_url).'" alt="" style="width:2.5rem;height:2.5rem;border-radius:.45rem;object-fit:cover;flex:none" />'
            .'<span style="min-width:0;overflow:hidden;text-overflow:ellipsis">'.$name.'</span>'
            .'</span>';
    }

    /** @return array<int, string> */
    private static function imageMediaOptions(): array
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->get()
            ->mapWithKeys(fn (Media $media): array => [$media->getKey() => static::mediaOptionLabel($media)])
            ->all();
    }

    private static function mediaPreview(?int $mediaId, mixed $zoom = 100, mixed $positionX = 50, mixed $positionY = 50): HtmlString
    {
        $media = $mediaId ? Media::query()->find($mediaId) : null;

        if ($media === null || blank($media->thumbnail_url)) {
            return new HtmlString('<div style="display:grid;min-height:13rem;place-items:center;border:1px dashed rgb(100 116 139 / .45);border-radius:.75rem;padding:1rem;color:rgb(148 163 184);font-size:.875rem">Aucune image sélectionnée.</div>');
        }

        $zoom = max(50, min(200, is_numeric($zoom) ? (int) $zoom : 100));
        $positionX = max(0, min(100, is_numeric($positionX) ? (int) $positionX : 50));
        $positionY = max(0, min(100, is_numeric($positionY) ? (int) $positionY : 50));
        $scale = $zoom / 100;

        return new HtmlString(
            '<figure style="overflow:hidden;border:1px solid rgb(100 116 139 / .35);border-radius:.75rem;background:rgb(15 23 42 / .18)">'
            .'<div style="height:16rem;overflow:hidden;background:rgb(241 245 249)"><img src="'.e($media->thumbnail_url).'" alt="'.e($media->alt_text ?: $media->original_name).'" style="display:block;width:100%;height:100%;object-fit:contain;object-position:'.$positionX.'% '.$positionY.'%;transform:scale('.$scale.');transform-origin:'.$positionX.'% '.$positionY.'%" /></div>'
            .'<figcaption style="padding:.7rem .85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.75rem;color:rgb(148 163 184)">'.e($media->original_name).'</figcaption>'
            .'</figure>',
        );
    }
}
