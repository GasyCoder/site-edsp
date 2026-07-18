<?php

namespace App\Filament\Resources\AcademicLevels;

use App\Filament\Resources\AcademicLevels\Pages\ManageAcademicLevels;
use App\Models\AcademicLevel;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AcademicLevelResource extends Resource
{
    protected static ?string $model = AcademicLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Niveaux L1–M2';

    protected static ?string $modelLabel = 'niveau';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Niveau académique')->schema([
                Grid::make(3)->schema([
                    TextInput::make('code')->label('Code')->required()->unique(ignoreRecord: true)->maxLength(10),
                    TextInput::make('nom')->label('Libellé')->required()->maxLength(100),
                    TextInput::make('ordre')->label('Ordre')->numeric()->minValue(1)->required(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('ordre')->columns([
            TextColumn::make('ordre')->label('#')->sortable(),
            TextColumn::make('code')->label('Niveau')->badge()->searchable(),
            TextColumn::make('nom')->label('Libellé')->searchable(),
            TextColumn::make('parcours_links_count')->label('Parcours')->counts('parcoursLinks'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAcademicLevels::route('/')];
    }
}
