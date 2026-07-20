<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Forms\MediaImagePreview;
use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Models\Media;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Établissement';

    protected static ?string $navigationLabel = 'Équipe pédagogique';

    protected static ?string $modelLabel = 'membre de l’équipe';

    protected static ?string $pluralModelLabel = 'équipe pédagogique';

    protected static ?string $recordTitleAttribute = 'last_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité et fonction')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('first_name')
                                ->label('Prénom')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('last_name')
                                ->label('Nom')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('position')
                                ->label('Fonction')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('display_order')
                                ->label('Ordre d’affichage')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Select::make('photo_id')
                                ->label('Photo')
                                ->helperText('La vignette dans la liste et l’aperçu permettent de vérifier la photo avant d’enregistrer.')
                                ->relationship(
                                    name: 'photo',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaImagePreview::optionLabel($record))
                                ->allowHtml()
                                ->live()
                                ->searchable()
                                ->preload(),
                            Placeholder::make('photo_preview')
                                ->label('Aperçu de la photo')
                                ->content(fn (Get $get) => MediaImagePreview::render($get->integer('photo_id'), 'Aucune photo sélectionnée.')),
                        ]),
                        Textarea::make('biography')
                            ->label('Biographie')
                            ->rows(6)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
                Section::make('Contact et présence en ligne')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Adresse e-mail')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->tel()
                                ->maxLength(50),
                        ]),
                        KeyValue::make('social_links')
                            ->label('Réseaux et liens')
                            ->keyLabel('Réseau')
                            ->valueLabel('URL')
                            ->addActionLabel('Ajouter un lien')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Version anglaise')
                    ->description('Fonction et biographie affichées lorsque le visiteur choisit English.')
                    ->schema([
                        TextInput::make('translations.en.position')->label('Fonction en anglais')->maxLength(255),
                        Textarea::make('translations.en.biography')->label('Biographie en anglais')->rows(6)->maxLength(5000),
                    ])
                    ->collapsed(),
                Section::make('Publication')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options(self::statusOptions())
                            ->default('draft')
                            ->required(),
                        Toggle::make('is_visible')
                            ->label('Afficher sur le site')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->defaultSort('display_order')
            ->columns([
                ImageColumn::make('photo.path')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('last_name')
                    ->label('Membre')
                    ->formatStateUsing(fn (TeamMember $record): string => $record->full_name)
                    ->description(fn (TeamMember $record): string => $record->position)
                    ->searchable(['first_name', 'last_name', 'position'])
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
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
                TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statusOptions()),
                TernaryFilter::make('is_visible')
                    ->label('Visibilité'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('6xl'),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
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
            'index' => ManageTeamMembers::route('/'),
        ];
    }
}
