<?php

namespace App\Filament\Resources\Semesters;

use App\Filament\Resources\Semesters\Pages\ManageSemesters;
use App\Models\Semester;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Semestres';

    protected static ?string $modelLabel = 'semestre';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Semestre')->schema([
                Grid::make(3)->schema([
                    TextInput::make('code')->label('Code')->required()->unique(ignoreRecord: true)->maxLength(10),
                    TextInput::make('nom')->label('Libellé')->required()->maxLength(100),
                    TextInput::make('ordre')->label('Ordre')->numeric()->minValue(1)->required(),
                ]),
                Toggle::make('is_active')->label('Semestre actif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('ordre')->columns([
            TextColumn::make('ordre')->label('#')->sortable(),
            TextColumn::make('code')->label('Semestre')->badge()->searchable(),
            TextColumn::make('nom')->label('Libellé')->searchable(),
            IconColumn::make('is_active')->label('Actif')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSemesters::route('/')];
    }
}
