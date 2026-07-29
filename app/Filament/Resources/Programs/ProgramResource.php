<?php

namespace App\Filament\Resources\Programs;

use App\Enums\ContentStatus;
use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Forms\SeoPreview;
use App\Filament\Resources\Programs\Pages\CreateProgram;
use App\Filament\Resources\Programs\Pages\EditProgram;
use App\Filament\Resources\Programs\Pages\ManagePrograms;
use App\Models\Media;
use App\Models\Mention;
use App\Models\Program;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use UnitEnum;

class ProgramResource extends Resource
{
    use HasPublicationActions;

    protected static ?string $model = Program::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Offre de formation';

    protected static ?string $navigationLabel = 'Présentation des mentions';

    protected static ?string $modelLabel = 'page de mention';

    protected static ?string $pluralModelLabel = 'pages des mentions';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->description('La mention, les parcours et les niveaux proviennent directement des données de Scolarité.')
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
                            Select::make('mention_id')
                                ->label('Mention')
                                ->relationship(
                                    name: 'mentionRecord',
                                    titleAttribute: 'nom',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('is_active', true)->orderBy('nom'),
                                )
                                ->required()
                                ->live()
                                ->unique(ignoreRecord: true)
                                ->searchable()
                                ->preload()
                                ->helperText('Référentiel administré dans Scolarité → Mentions.'),
                            Placeholder::make('academic_offer')
                                ->label('Organisation issue de Scolarité')
                                ->content(fn (Get $get): HtmlString => static::academicOfferPreview($get->integer('mention_id')))
                                ->columnSpanFull(),
                            Hidden::make('level')
                                ->default('L1 à M2'),
                            TextInput::make('duration')
                                ->label('Durée'),
                            TextInput::make('manager')
                                ->label('Responsable'),
                            Select::make('image_id')
                                ->label('Image')
                                ->helperText('Une vignette est affichée dans la liste et un aperçu complet apparaît après la sélection.')
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
                                ->label('Aperçu de l’image')
                                ->content(fn (Get $get): HtmlString => static::mediaPreview($get->integer('image_id'), 'Aucune image principale sélectionnée.')),
                        ]),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4),
                    ]),
                Section::make('Détails pédagogiques')
                    ->schema([
                        RichEditor::make('objectives')
                            ->label('Objectifs')
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('admission_requirements')
                            ->label('Conditions d’admission')
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('skills')
                            ->label('Compétences')
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('careers')
                            ->label('Débouchés')
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('curriculum')
                            ->label('Programme')
                            ->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? ''))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Publication et SEO')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options(fn (): array => static::publicationStatusOptions())
                                ->disabled(fn (?Program $record): bool => $record !== null && ! static::canManagePublicationStatus())
                                ->default('draft')
                                ->required(),
                            DateTimePicker::make('published_at')
                                ->label('Publication')
                                ->disabled(fn (?Program $record): bool => $record !== null && ! static::canManagePublicationStatus())
                                ->required(fn (Get $get): bool => $get->string('status') === 'scheduled')
                                ->seconds(false),
                            TextInput::make('position')
                                ->label('Ordre')
                                ->numeric()
                                ->minValue(0),
                            TextInput::make('meta_title')
                                ->label('Titre SEO')
                                ->maxLength(70),
                        ]),
                        Textarea::make('meta_description')
                            ->label('Description SEO')
                            ->rows(3)
                            ->maxLength(180),
                        Grid::make(2)->schema([
                            TextInput::make('meta_keywords')
                                ->label('Mots-clés'),
                            TextInput::make('canonical_url')
                                ->label('URL canonique')
                                ->rules([new SafeUrl]),
                            Toggle::make('robots_index')
                                ->label('Autoriser l’indexation')
                                ->default(true),
                            Toggle::make('robots_follow')
                                ->label('Suivre les liens')
                                ->default(true),
                            TextInput::make('og_title')
                                ->label('Titre Open Graph')
                                ->maxLength(95),
                            Textarea::make('og_description')
                                ->label('Description Open Graph')
                                ->rows(2)
                                ->maxLength(200),
                            Select::make('og_image_id')
                                ->label('Image Open Graph')
                                ->relationship(
                                    name: 'ogImage',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->getOptionLabelFromRecordUsing(fn (Media $record): string => static::mediaOptionLabel($record))
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('og_image_preview')
                                ->label('Aperçu Open Graph')
                                ->content(fn (Get $get): HtmlString => static::mediaPreview($get->integer('og_image_id'), 'Aucune image Open Graph sélectionnée.')),
                        ]),
                        ...SeoPreview::components('formations'),
                    ])
                    ->collapsible(),
                Section::make('Documents liés')
                    ->schema([
                        Select::make('documents')
                            ->label('Documents')
                            ->relationship('documents', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->collapsible(),
                Section::make('Version anglaise')
                    ->description('Traduction professionnelle affichée sur la version English du site.')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('translations.en.title')->label('Programme title')->maxLength(180),
                            TextInput::make('translations.en.duration')->label('Duration'),
                            TextInput::make('translations.en.manager')->label('Programme leader'),
                        ]),
                        Textarea::make('translations.en.description')->label('Description')->rows(4),
                        RichEditor::make('translations.en.objectives')->label('Objectives')->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('translations.en.admission_requirements')->label('Entry requirements')->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('translations.en.skills')->label('Skills')->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('translations.en.careers')->label('Career opportunities')->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        RichEditor::make('translations.en.curriculum')->label('Curriculum')->dehydrateStateUsing(fn (?string $state): string => Purifier::clean($state ?? '')),
                        Grid::make(2)->schema([
                            TextInput::make('translations.en.meta_title')->label('SEO title')->maxLength(70),
                            Textarea::make('translations.en.meta_description')->label('SEO description')->rows(3)->maxLength(180),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('mentionRecord.parcours.levelLinks.level'))
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Formation')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mentionRecord.nom')
                    ->label('Mention')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academic_offer')
                    ->label('Parcours · niveaux')
                    ->state(fn (Program $record): array => $record->mentionRecord?->parcours
                        ->map(fn ($parcours): string => $parcours->nom.' — '.$parcours->levelLinks
                            ->where('is_active', true)
                            ->sortBy('level.ordre')
                            ->pluck('level.code')
                            ->filter()
                            ->join(', '))
                        ->values()
                        ->all() ?? [])
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => ($state instanceof ContentStatus ? $state : ContentStatus::tryFrom((string) $state))?->label() ?? (string) $state)
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
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
                SelectFilter::make('mention_id')
                    ->label('Mention')
                    ->relationship('mentionRecord', 'nom'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                ...static::publicationActions(),
                EditAction::make()
                    ->url(fn (Program $record): string => static::getUrl('edit', ['record' => $record])),
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
            'index' => ManagePrograms::route('/'),
            'create' => CreateProgram::route('/create'),
            'edit' => EditProgram::route('/{record}/edit'),
        ];
    }

    private static function academicOfferPreview(?int $mentionId): HtmlString
    {
        if (! $mentionId) {
            return new HtmlString('<span class="text-sm text-gray-500">Choisissez une mention pour afficher ses parcours.</span>');
        }

        $mention = Mention::query()
            ->with(['parcours.levelLinks' => fn ($query) => $query->where('is_active', true)->with('level')])
            ->find($mentionId);

        if ($mention === null || $mention->parcours->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-500">Aucun parcours actif pour cette mention.</span>');
        }

        $items = $mention->parcours->map(function ($parcours): string {
            $levels = $parcours->levelLinks
                ->sortBy('level.ordre')
                ->map(fn ($link): string => e($link->level?->code).($link->is_common_core ? ' · tronc commun' : ''))
                ->filter()
                ->join(', ');

            return '<li><strong>'.e($parcours->nom).'</strong> — '.$levels.'</li>';
        })->join('');

        return new HtmlString('<ul class="list-disc space-y-2 pl-5 text-sm">'.$items.'</ul>');
    }

    private static function mediaOptionLabel(Media $media): string
    {
        $thumbnail = $media->thumbnail_url;
        $name = e($media->original_name);

        if (blank($thumbnail)) {
            return $name;
        }

        return '<span style="display:flex;align-items:center;gap:.75rem">'
            .'<img src="'.e($thumbnail).'" alt="" style="width:2.5rem;height:2.5rem;border-radius:.45rem;object-fit:cover;flex:none" />'
            .'<span style="min-width:0;overflow:hidden;text-overflow:ellipsis">'.$name.'</span>'
            .'</span>';
    }

    private static function mediaPreview(?int $mediaId, string $emptyMessage): HtmlString
    {
        $media = $mediaId ? Media::query()->find($mediaId) : null;
        $imageUrl = $media?->thumbnail_url;

        if ($media === null || blank($imageUrl)) {
            return new HtmlString('<div style="display:grid;min-height:10rem;place-items:center;border:1px dashed rgb(100 116 139 / .45);border-radius:.75rem;padding:1rem;color:rgb(148 163 184);font-size:.875rem">'.e($emptyMessage).'</div>');
        }

        $dimensions = $media->width && $media->height ? e($media->width.' × '.$media->height.' px') : null;

        return new HtmlString(
            '<figure style="overflow:hidden;border:1px solid rgb(100 116 139 / .35);border-radius:.75rem;background:rgb(15 23 42 / .18)">'
            .'<img src="'.e($imageUrl).'" alt="'.e($media->alt_text ?: $media->original_name).'" style="display:block;width:100%;height:13rem;object-fit:cover" />'
            .'<figcaption style="display:flex;justify-content:space-between;gap:1rem;padding:.7rem .85rem;font-size:.75rem;color:rgb(148 163 184)">'
            .'<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'.e($media->original_name).'</span>'
            .($dimensions ? '<span style="white-space:nowrap">'.$dimensions.'</span>' : '')
            .'</figcaption></figure>',
        );
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
