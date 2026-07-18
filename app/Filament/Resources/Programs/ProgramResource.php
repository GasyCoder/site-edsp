<?php

namespace App\Filament\Resources\Programs;

use App\Enums\ContentStatus;
use App\Filament\Concerns\HasPublicationActions;
use App\Filament\Forms\SeoPreview;
use App\Filament\Resources\Programs\Pages\ManagePrograms;
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
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use UnitEnum;

class ProgramResource extends Resource
{
    use HasPublicationActions;

    protected static ?string $model = Program::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Offre de formation';

    protected static ?string $navigationLabel = 'Formations';

    protected static ?string $modelLabel = 'formation';

    protected static ?string $pluralModelLabel = 'formations';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
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
                            Select::make('department_id')
                                ->label('Département')
                                ->relationship('department', 'name')
                                ->searchable()
                                ->preload(),
                            TextInput::make('level')
                                ->label('Niveau')
                                ->required()
                                ->maxLength(100),
                            TextInput::make('domain')
                                ->label('Domaine'),
                            TextInput::make('mention')
                                ->label('Mention'),
                            TextInput::make('track')
                                ->label('Parcours'),
                            TextInput::make('duration')
                                ->label('Durée'),
                            TextInput::make('manager')
                                ->label('Responsable'),
                            Select::make('image_id')
                                ->label('Image')
                                ->relationship(
                                    name: 'image',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->searchable()
                                ->preload(),
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
                                ->searchable()
                                ->preload(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Formation')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label('Niveau')
                    ->badge(),
                TextColumn::make('department.name')
                    ->label('Département')
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
                SelectFilter::make('department_id')
                    ->label('Département')
                    ->relationship('department', 'name'),
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
            'index' => ManagePrograms::route('/'),
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
