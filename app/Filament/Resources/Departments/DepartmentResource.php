<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ManageDepartments;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Établissement';

    protected static ?string $navigationLabel = 'Départements';

    protected static ?string $modelLabel = 'département';

    protected static ?string $pluralModelLabel = 'départements';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Département')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label('Identifiant URL')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Utilisé dans les adresses publiques du site.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(6)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Version anglaise')
                    ->description('Contenu affiché lorsque le visiteur choisit English.')
                    ->schema([
                        TextInput::make('translations.en.name')->label('Nom en anglais')->maxLength(255),
                        Textarea::make('translations.en.description')->label('Description en anglais')->rows(5)->maxLength(3000),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->description(fn (Department $record): ?string => filled($record->description) ? Str::limit($record->description, 80) : null)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identifiant URL')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('programs_count')
                    ->label('Formations')
                    ->counts('programs')
                    ->badge()
                    ->sortable(),
                TextColumn::make('team_members_count')
                    ->label('Membres')
                    ->counts('teamMembers')
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageDepartments::route('/'),
        ];
    }
}
