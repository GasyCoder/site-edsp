<?php

namespace App\Filament\Resources\TeachingUnits;

use App\Filament\Resources\TeachingUnits\Pages\ManageTeachingUnits;
use App\Models\ParcoursLevel;
use App\Models\TeachingUnit;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TeachingUnitResource extends Resource
{
    protected static ?string $model = TeachingUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Unités d’enseignement';

    protected static ?string $modelLabel = 'unité d’enseignement';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Unité d’enseignement (UE)')->schema([
                Select::make('parcours_level_id')
                    ->label('Parcours et niveau')
                    ->relationship('parcoursLevel', 'id')
                    ->getOptionLabelFromRecordUsing(fn (ParcoursLevel $record): string => $record->label)
                    ->searchable(['id'])
                    ->preload()
                    ->required(),
                Select::make('semestre_id')->label('Semestre')->relationship('semester', 'nom')->searchable()->preload()->required(),
                Grid::make(3)->schema([
                    TextInput::make('code')->label('Code UE')->required()->maxLength(50),
                    TextInput::make('nom')->label('Intitulé')->required()->maxLength(255)->columnSpan(2),
                ]),
                TextInput::make('credits')->label('Crédits ECTS')->numeric()->minValue(0)->maxValue(60),
                Toggle::make('is_active')->label('UE active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Code')->badge()->searchable(),
            TextColumn::make('nom')->label('Unité d’enseignement')->searchable()->wrap(),
            TextColumn::make('parcoursLevel.parcours.nom')->label('Parcours')->searchable(),
            TextColumn::make('parcoursLevel.level.code')->label('Niveau')->badge(),
            TextColumn::make('semester.code')->label('Semestre')->badge(),
            TextColumn::make('course_elements_count')->label('EC')->counts('courseElements'),
            IconColumn::make('is_active')->label('Active')->boolean(),
        ])->filters([
            SelectFilter::make('semestre_id')->label('Semestre')->relationship('semester', 'nom'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTeachingUnits::route('/')];
    }
}
