<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Forms\MediaImagePreview;
use App\Filament\Resources\Partners\Pages\ManagePartners;
use App\Models\Media;
use App\Models\Partner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Contenus';

    protected static ?string $navigationLabel = 'Partenaires';

    protected static ?string $modelLabel = 'partenaire';

    protected static ?string $pluralModelLabel = 'partenaires';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Partenaire')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('Site web')
                            ->url()
                            ->prefixIcon(Heroicon::OutlinedGlobeAlt)
                            ->maxLength(255),
                        Select::make('logo_id')
                            ->label('Logo')
                            ->helperText('Vérifiez le logo sélectionné dans l’aperçu avant l’enregistrement.')
                            ->relationship(
                                name: 'logo',
                                titleAttribute: 'original_name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaImagePreview::optionLabel($record))
                            ->allowHtml()
                            ->live()
                            ->searchable()
                            ->preload(),
                        Placeholder::make('logo_preview')
                            ->label('Aperçu du logo')
                            ->content(fn (Get $get) => MediaImagePreview::render($get->integer('logo_id'), 'Aucun logo sélectionné.')),
                        TextInput::make('position')
                            ->label('Ordre d’affichage')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_visible')
                            ->label('Afficher sur le site')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Version anglaise')
                    ->description('Nom affiché lorsque le visiteur choisit English.')
                    ->schema([
                        TextInput::make('translations.en.name')->label('Nom en anglais')->maxLength(255),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('position')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('logo'))
            ->columns([
                ImageColumn::make('logo_preview')
                    ->label('Logo')
                    ->state(fn (Partner $record): ?string => $record->logo?->thumbnail_url)
                    ->height(42),
                TextColumn::make('name')
                    ->label('Partenaire')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('Site web')
                    ->url(fn (Partner $record): ?string => $record->url, shouldOpenInNewTab: true)
                    ->limit(45)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibilité'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('4xl'),
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

    public static function getPages(): array
    {
        return [
            'index' => ManagePartners::route('/'),
        ];
    }
}
