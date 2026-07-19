<?php

namespace App\Filament\Resources\Parcours;

use App\Filament\Resources\Parcours\Pages\ManageParcours;
use App\Models\Parcours;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ParcoursResource extends Resource
{
    protected static ?string $model = Parcours::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Parcours';

    protected static ?string $modelLabel = 'parcours';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Parcours de formation')->schema([
                Select::make('mention_id')->label('Mention')->relationship('mention', 'nom')->searchable()->preload()->required(),
                Grid::make(2)->schema([
                    TextInput::make('code')->label('Code')->required()->unique(ignoreRecord: true)->maxLength(30),
                    TextInput::make('nom')->label('Nom du parcours')->required()->maxLength(255),
                ]),
                Textarea::make('description')->label('Description')->rows(4)->columnSpanFull(),
            ]),
            Section::make('Version anglaise')->description('Contenu affiché lorsque le visiteur choisit English.')->schema([
                TextInput::make('translations.en.nom')->label('Nom du parcours en anglais')->maxLength(255),
                Textarea::make('translations.en.description')->label('Description en anglais')->rows(4),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Code')->badge()->searchable()->sortable(),
            TextColumn::make('nom')->label('Parcours')->searchable()->sortable(),
            TextColumn::make('mention.nom')->label('Mention')->badge()->sortable(),
            TextColumn::make('level_links_count')->label('Niveaux')->counts('levelLinks'),
        ])->filters([
            SelectFilter::make('mention')->relationship('mention', 'nom'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageParcours::route('/')];
    }
}
