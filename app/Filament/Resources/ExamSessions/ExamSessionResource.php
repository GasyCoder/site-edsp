<?php

namespace App\Filament\Resources\ExamSessions;

use App\Filament\Resources\ExamSessions\Pages\ManageExamSessions;
use App\Models\ExamSession;
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

class ExamSessionResource extends Resource
{
    protected static ?string $model = ExamSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Sessions d’examens';

    protected static ?string $modelLabel = 'session d’examen';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Session d’examen')->schema([
                Grid::make(2)->schema([
                    TextInput::make('code')->label('Code')->required()->unique(ignoreRecord: true)->maxLength(30),
                    TextInput::make('nom')->label('Libellé')->required()->maxLength(255),
                ]),
                Grid::make(2)->schema([
                    Toggle::make('is_rattrapage')->label('Session de rattrapage'),
                    Toggle::make('is_active')->label('Session active'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Code')->badge()->searchable(),
            TextColumn::make('nom')->label('Session')->searchable(),
            IconColumn::make('is_rattrapage')->label('Rattrapage')->boolean(),
            IconColumn::make('is_active')->label('Active')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageExamSessions::route('/')];
    }
}
