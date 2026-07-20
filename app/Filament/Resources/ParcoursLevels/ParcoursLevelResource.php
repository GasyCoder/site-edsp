<?php

namespace App\Filament\Resources\ParcoursLevels;

use App\Filament\Resources\ParcoursLevels\Pages\ManageParcoursLevels;
use App\Models\ParcoursLevel;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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

class ParcoursLevelResource extends Resource
{
    protected static ?string $model = ParcoursLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Parcours par niveau';

    protected static ?string $modelLabel = 'offre par niveau';

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Association parcours–niveau')->description('Définit les niveaux proposés pour chaque parcours.')->schema([
                Grid::make(2)->schema([
                    Select::make('parcours_id')->label('Parcours')->relationship('parcours', 'nom')->searchable()->preload()->required(),
                    Select::make('level_id')->label('Niveau')->relationship('level', 'nom')->searchable()->preload()->required(),
                ]),
                Toggle::make('is_active')->label('Association active')->default(true),
                Toggle::make('is_common_core')
                    ->label('Tronc commun')
                    ->helperText('Activez uniquement lorsqu’un niveau constitue le tronc commun de la mention.')
                    ->default(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('parcours.nom')->label('Parcours')->searchable()->sortable(),
            TextColumn::make('parcours.mention.nom')->label('Mention')->badge(),
            TextColumn::make('level.code')->label('Niveau')->badge()->sortable(),
            IconColumn::make('is_common_core')
                ->label('Tronc commun')
                ->boolean(),
            TextColumn::make('teaching_units_count')->label('UE')->counts('teachingUnits'),
            IconColumn::make('is_active')->label('Active')->boolean(),
        ])->filters([
            SelectFilter::make('parcours')->relationship('parcours', 'nom'),
            SelectFilter::make('level')->relationship('level', 'nom'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageParcoursLevels::route('/')];
    }
}
