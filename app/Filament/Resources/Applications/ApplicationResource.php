<?php

namespace App\Filament\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Exports\ApplicationExporter;
use App\Filament\Resources\Applications\Pages\ManageApplications;
use App\Models\Application;
use App\Services\ActivityLogger;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
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

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'Préinscriptions';

    protected static ?string $modelLabel = 'dossier';

    protected static ?string $pluralModelLabel = 'dossiers de préinscription';

    protected static ?string $recordTitleAttribute = 'application_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dossier')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('application_number')
                                ->label('Numéro de dossier')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('submitted_at')
                                ->label('Soumis le')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('admission_campaign_id')
                                ->label('Campagne')
                                ->relationship('campaign', 'title')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('program_id')
                                ->label('Formation')
                                ->relationship('program', 'title')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),
                Section::make('Candidat')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('first_name')
                                ->label('Prénom')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('last_name')
                                ->label('Nom')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('email')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->disabled()
                                ->dehydrated(false),
                            DatePicker::make('birth_date')
                                ->label('Date de naissance')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('address')
                                ->label('Adresse')
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                        Textarea::make('academic_background')
                            ->label('Parcours académique')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(4),
                    ]),
                Section::make('Traitement administratif')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'submitted' => 'Soumis',
                                'under_review' => 'En vérification',
                                'incomplete' => 'Incomplet',
                                'eligible' => 'Recevable',
                                'accepted' => 'Accepté',
                                'rejected' => 'Refusé',
                                'waitlisted' => 'Liste d’attente',
                                'archived' => 'Archivé',
                            ])
                            ->disabled(fn (?Application $record): bool => $record === null || ! (auth()->user()?->can('changeStatus', $record) ?? false))
                            ->required(),
                        Textarea::make('internal_notes')
                            ->label('Notes internes')
                            ->rows(5),
                    ]),
                Section::make('Pièces justificatives')
                    ->schema([
                        Placeholder::make('documents_overview')
                            ->hiddenLabel()
                            ->content(fn (?Application $record) => $record === null
                                ? null
                                : view('filament.applications.documents', [
                                    'documents' => $record->documents()->orderBy('created_at')->get(),
                                    'canDownload' => auth()->user()?->can('downloadDocuments', $record) ?? false,
                                ])),
                    ])
                    ->collapsible(),
                Section::make('Historique des statuts')
                    ->schema([
                        Placeholder::make('status_history_overview')
                            ->hiddenLabel()
                            ->content(fn (?Application $record) => $record === null
                                ? null
                                : view('filament.applications.status-history', [
                                    'history' => $record->statusHistory()->with('changedBy')->get(),
                                ])),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('application_number')
            ->columns([
                TextColumn::make('application_number')
                    ->label('Dossier')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('last_name')
                    ->label('Candidat')
                    ->formatStateUsing(fn (Application $record): string => "{$record->last_name} {$record->first_name}")
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('program.title')
                    ->label('Formation')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => ($state instanceof ApplicationStatus ? $state : ApplicationStatus::tryFrom((string) $state))?->label() ?? (string) $state)
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Soumis',
                        'under_review' => 'En vérification',
                        'incomplete' => 'Incomplet',
                        'eligible' => 'Recevable',
                        'accepted' => 'Accepté',
                        'rejected' => 'Refusé',
                        'waitlisted' => 'Liste d’attente',
                        'archived' => 'Archivé',
                    ]),
                SelectFilter::make('program_id')
                    ->label('Formation')
                    ->relationship('program', 'title'),
                SelectFilter::make('admission_campaign_id')
                    ->label('Campagne')
                    ->relationship('campaign', 'title'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data, Application $record): array {
                        $data['internal_notes'] = $record->internal_notes;
                        app(ActivityLogger::class)->record(
                            'application.consulted',
                            $record,
                            auth()->id(),
                        );

                        return $data;
                    }),
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Application $record): array {
                        $data['internal_notes'] = $record->internal_notes;

                        return $data;
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exporter les dossiers')
                    ->authorize(fn (): bool => auth()->user()?->can('exportAny', Application::class) ?? false)
                    ->exporter(ApplicationExporter::class)
                    ->fileName(fn (): string => 'preinscriptions-edsp-'.now()->format('Y-m-d-His')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageApplications::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
