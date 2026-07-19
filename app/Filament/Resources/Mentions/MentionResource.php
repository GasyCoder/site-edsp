<?php

namespace App\Filament\Resources\Mentions;

use App\Filament\Resources\Mentions\Pages\ManageMentions;
use App\Models\Mention;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
use Filament\Tables\Table;
use UnitEnum;

class MentionResource extends Resource
{
    protected static ?string $model = Mention::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Scolarité';

    protected static ?string $navigationLabel = 'Mentions';

    protected static ?string $modelLabel = 'mention';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identification de la mention')->schema([
                Grid::make(2)->schema([
                    TextInput::make('code')->label('Code')->required()->unique(ignoreRecord: true)->maxLength(30),
                    TextInput::make('nom')->label('Nom')->required()->maxLength(255),
                ]),
                Textarea::make('description')->label('Description')->rows(4)->columnSpanFull(),
                Toggle::make('is_active')->label('Mention active')->default(true),
            ]),
            Section::make('Version anglaise')->description('Contenu affiché lorsque le visiteur choisit English.')->schema([
                TextInput::make('translations.en.nom')->label('Nom en anglais')->maxLength(255),
                Textarea::make('translations.en.description')->label('Description en anglais')->rows(4),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Code')->badge()->searchable()->sortable(),
            TextColumn::make('nom')->label('Mention')->searchable()->sortable(),
            TextColumn::make('parcours_count')->label('Parcours')->counts('parcours'),
            IconColumn::make('is_active')->label('Active')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->requiresConfirmation(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMentions::route('/')];
    }
}
