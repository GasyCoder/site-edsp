<?php

namespace App\Filament\Resources\CourseElements;

use App\Filament\Resources\CourseElements\Pages\ManageCourseElements;
use App\Models\CourseElement;
use App\Models\TeachingUnit;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class CourseElementResource extends Resource
{
    protected static ?string $model = CourseElement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Éléments constitutifs';

    protected static ?string $modelLabel = 'élément constitutif';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Élément constitutif (EC)')->schema([
                Select::make('ue_id')
                    ->label('Unité d’enseignement')
                    ->relationship('teachingUnit', 'nom')
                    ->getOptionLabelFromRecordUsing(fn (TeachingUnit $record): string => $record->label)
                    ->searchable(['code', 'nom'])
                    ->preload()
                    ->required(),
                Grid::make(3)->schema([
                    TextInput::make('code')->label('Code EC')->required()->maxLength(50),
                    TextInput::make('nom')->label('Intitulé')->required()->maxLength(255)->columnSpan(2),
                ]),
                TextInput::make('coefficient')->label('Coefficient')->numeric()->minValue(0)->step(0.01)->required(),
                Grid::make(2)->schema([
                    Toggle::make('is_active')->label('EC actif')->default(true),
                    Toggle::make('is_historical_marker')->label('Ancien EC / repère historique')->live(),
                ]),
                Select::make('replaced_by_ec_id')->label('Remplacé par')->relationship('replacedBy', 'nom')->searchable()->preload(),
                Textarea::make('historical_comment')->label('Commentaire historique')->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Code')->badge()->searchable(),
            TextColumn::make('nom')->label('Élément constitutif')->searchable()->wrap(),
            TextColumn::make('teachingUnit.code')->label('UE')->badge()->searchable(),
            TextColumn::make('teachingUnit.parcoursLevel.level.code')->label('Niveau')->badge(),
            TextColumn::make('coefficient')->label('Coefficient')->numeric(decimalPlaces: 2),
            IconColumn::make('is_active')->label('Actif')->boolean(),
            IconColumn::make('is_historical_marker')->label('Historique')->boolean(),
        ])->filters([
            TernaryFilter::make('is_active')->label('État actif'),
            TernaryFilter::make('is_historical_marker')->label('Repère historique'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCourseElements::route('/')];
    }
}
