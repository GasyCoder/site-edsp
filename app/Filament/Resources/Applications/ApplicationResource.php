<?php

namespace App\Filament\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Exports\ApplicationExporter;
use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\ManageApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Models\Application;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Grid as TableGrid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'Inscriptions';

    protected static ?string $modelLabel = 'dossier';

    protected static ?string $pluralModelLabel = 'dossiers d’inscription';

    protected static ?string $recordTitleAttribute = 'application_number';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'academicLevel',
            'campaign',
            'mention',
            'parcours',
            'program',
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make('Repères du dossier')
                    ->description('Les informations essentielles restent visibles pendant le traitement.')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('application_number_summary')
                                    ->label('Numéro de dossier')
                                    ->content(fn (?Application $record): string => $record?->application_number ?? '—'),
                                Placeholder::make('submitted_at_summary')
                                    ->label('Reçu le')
                                    ->content(fn (?Application $record): string => $record?->submitted_at?->format('d/m/Y à H:i') ?? '—'),
                                Placeholder::make('candidate_summary')
                                    ->label('Candidat')
                                    ->content(fn (?Application $record): string => trim(($record?->last_name ?? '').' '.($record?->first_name ?? '')) ?: '—'),
                                Placeholder::make('contact_summary')
                                    ->label('Coordonnées')
                                    ->content(fn (?Application $record): string => collect([$record?->email, $record?->phone])->filter()->implode(' · ') ?: '—'),
                                Placeholder::make('orientation_summary')
                                    ->label('Orientation demandée')
                                    ->content(fn (?Application $record): string => collect([
                                        $record?->program?->title,
                                        $record?->academicLevel?->code,
                                        $record?->mention?->nom,
                                        $record?->parcours?->nom,
                                    ])->filter()->implode(' · ') ?: 'Non renseignée'),
                                Placeholder::make('campaign_summary')
                                    ->label('Campagne')
                                    ->content(fn (?Application $record): string => $record?->campaign?->title ?? '—'),
                            ]),
                    ])
                    ->columnSpan(['default' => 12, 'xl' => 8]),
                Section::make('Traitement administratif')
                    ->description('Mettez à jour le suivi du dossier. Le candidat est informé lors d’un changement de statut.')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
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
                            ->helperText('Ces notes restent réservées à l’équipe administrative.')
                            ->rows(5),
                    ])
                    ->columnSpan(['default' => 12, 'xl' => 4]),
                Section::make('Pièces justificatives')
                    ->description('Consultez directement les images et PDF transmis par le candidat.')
                    ->icon(Heroicon::OutlinedPaperClip)
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
                    ->columnSpan(['default' => 12, 'xl' => 8]),
                Section::make('Historique des statuts')
                    ->description('Chaque changement est horodaté et attribué à son auteur.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Placeholder::make('status_history_overview')
                            ->hiddenLabel()
                            ->content(fn (?Application $record) => $record === null
                                ? null
                                : view('filament.applications.status-history', [
                                    'history' => $record->statusHistory()->with('changedBy')->get(),
                                ])),
                    ])
                    ->columnSpan(['default' => 12, 'xl' => 4]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('application_number')
                                ->label('Numéro de dossier')
                                ->icon(Heroicon::OutlinedDocumentText)
                                ->weight(FontWeight::Bold)
                                ->copyable(),
                            TextEntry::make('status')
                                ->label('Statut')
                                ->badge()
                                ->formatStateUsing(fn (mixed $state): string => ($state instanceof ApplicationStatus ? $state : ApplicationStatus::tryFrom((string) $state))?->label() ?? (string) $state)
                                ->color(fn (mixed $state): string => self::statusColor($state)),
                            TextEntry::make('submitted_at')
                                ->label('Reçu le')
                                ->icon(Heroicon::OutlinedCalendarDays)
                                ->dateTime('d/m/Y à H:i'),
                            TextEntry::make('campaign.title')
                                ->label('Campagne')
                                ->icon(Heroicon::OutlinedBuildingLibrary)
                                ->placeholder('—'),
                        ]),
                    ])
                    ->compact(),
                Tabs::make('Détails du dossier')
                    ->persistTabInQueryString('section')
                    ->tabs([
                        Tab::make('Identité et contact')
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->schema([
                                Section::make('Candidat')
                                    ->schema([
                                        TextEntry::make('civility')->label('Civilité')->formatStateUsing(fn (?string $state): string => filled($state) ? ucfirst($state) : '—'),
                                        TextEntry::make('gender')->label('Genre')->formatStateUsing(fn (?string $state): string => filled($state) ? ucfirst($state) : '—'),
                                        TextEntry::make('first_name')->label('Prénom(s)')->placeholder('—'),
                                        TextEntry::make('last_name')->label('Nom')->weight(FontWeight::SemiBold)->placeholder('—'),
                                        TextEntry::make('birth_date')->label('Date de naissance')->date('d/m/Y')->placeholder('—'),
                                        TextEntry::make('birth_place')->label('Lieu de naissance')->placeholder('—'),
                                        TextEntry::make('nationality')->label('Nationalité')->placeholder('—'),
                                        TextEntry::make('national_id')->label('CIN ou passeport')->placeholder('Non renseigné'),
                                    ])->columns(2),
                                Section::make('Coordonnées')
                                    ->schema([
                                        TextEntry::make('email')->label('Adresse e-mail')->icon(Heroicon::OutlinedEnvelope)->copyable(),
                                        TextEntry::make('phone')->label('Téléphone')->icon(Heroicon::OutlinedPhone)->copyable(),
                                        TextEntry::make('address')->label('Adresse')->icon(Heroicon::OutlinedMapPin)->placeholder('—')->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                        Tab::make('Famille et répondant')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->schema([
                                Section::make('Informations parentales')
                                    ->schema([
                                        TextEntry::make('father_name')->label('Nom du père')->placeholder('Non renseigné'),
                                        TextEntry::make('mother_name')->label('Nom de la mère')->placeholder('Non renseigné'),
                                        TextEntry::make('parent_phone')->label('Téléphone des parents')->icon(Heroicon::OutlinedPhone)->placeholder('Non renseigné'),
                                    ])->columns(2),
                                Section::make('Tuteur ou répondant')
                                    ->schema([
                                        TextEntry::make('guardian_name')->label('Nom')->placeholder('Non renseigné'),
                                        TextEntry::make('guardian_relationship')->label('Lien avec le candidat')->placeholder('Non renseigné'),
                                        TextEntry::make('guardian_phone')->label('Téléphone')->icon(Heroicon::OutlinedPhone)->placeholder('Non renseigné'),
                                    ])->columns(2),
                            ]),
                        Tab::make('Formation demandée')
                            ->icon(Heroicon::OutlinedAcademicCap)
                            ->schema([
                                Section::make('Orientation pédagogique')
                                    ->schema([
                                        TextEntry::make('program.title')->label('Formation')->placeholder('—'),
                                        TextEntry::make('academicLevel.nom')->label('Niveau')->placeholder('Non renseigné'),
                                        TextEntry::make('mention.nom')->label('Mention')->placeholder('Non renseignée'),
                                        TextEntry::make('parcours.nom')->label('Parcours')->placeholder('Non renseigné'),
                                    ])->columns(2),
                                Section::make('Parcours antérieur')
                                    ->schema([
                                        TextEntry::make('last_diploma')->label('Dernier diplôme')->placeholder('Non renseigné'),
                                        TextEntry::make('graduation_year')->label('Année d’obtention')->placeholder('Non renseignée'),
                                        TextEntry::make('previous_institution')->label('Établissement précédent')->placeholder('Non renseigné'),
                                        TextEntry::make('academic_background')->label('Informations complémentaires')->placeholder('Non renseignées')->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                        Tab::make('Pièces justificatives')
                            ->icon(Heroicon::OutlinedPaperClip)
                            ->badge(fn (Application $record): int => $record->documents()->count())
                            ->schema([
                                ViewEntry::make('documents_preview')
                                    ->hiddenLabel()
                                    ->view('filament.applications.documents', fn (Application $record): array => [
                                        'documents' => $record->documents()->orderBy('created_at')->get(),
                                        'canDownload' => auth()->user()?->can('downloadDocuments', $record) ?? false,
                                    ]),
                            ]),
                        Tab::make('Suivi administratif')
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                            ->schema([
                                Section::make('Notes internes')
                                    ->schema([
                                        TextEntry::make('internal_notes')
                                            ->hiddenLabel()
                                            ->state(fn (Application $record): ?string => $record->internal_notes)
                                            ->placeholder('Aucune note interne pour ce dossier.'),
                                    ]),
                                Section::make('Historique des statuts')
                                    ->schema([
                                        ViewEntry::make('status_history')
                                            ->hiddenLabel()
                                            ->view('filament.applications.status-history', fn (Application $record): array => [
                                                'history' => $record->statusHistory()->with('changedBy')->get(),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('application_number')
            ->defaultSort('submitted_at', 'desc')
            ->searchPlaceholder('Nom, e-mail ou numéro de dossier…')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->contentGrid(fn (ManageApplications $livewire): ?array => $livewire->viewMode === 'grid'
                ? ['xl' => 2]
                : null)
            ->extraAttributes(fn (ManageApplications $livewire): array => [
                'class' => 'edsp-applications-table edsp-applications-view-'.$livewire->viewMode,
            ])
            ->recordClasses(fn (Application $record): string => 'edsp-application-row edsp-application-status-'.self::statusValue($record->status))
            ->columns([
                Stack::make([
                    Split::make([
                        Stack::make([
                            TextColumn::make('application_number')
                                ->label('Dossier')
                                ->icon(Heroicon::OutlinedDocumentText)
                                ->iconColor('primary')
                                ->color('primary')
                                ->weight(FontWeight::Bold)
                                ->searchable()
                                ->copyable()
                                ->copyMessage('Numéro de dossier copié'),
                            TextColumn::make('last_name')
                                ->label('Candidat')
                                ->formatStateUsing(fn (Application $record): string => "{$record->last_name} {$record->first_name}")
                                ->icon(Heroicon::OutlinedUserCircle)
                                ->iconColor('gray')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->searchable(['last_name', 'first_name'])
                                ->sortable()
                                ->wrap(),
                            TextColumn::make('email')
                                ->label('E-mail')
                                ->icon(Heroicon::OutlinedEnvelope)
                                ->iconColor('gray')
                                ->color('gray')
                                ->size(TextSize::Small)
                                ->searchable()
                                ->limit(42)
                                ->tooltip(fn (Application $record): string => $record->email),
                        ])->space(1),
                        Stack::make([
                            TextColumn::make('status')
                                ->label('Statut')
                                ->badge()
                                ->formatStateUsing(fn (mixed $state): string => ($state instanceof ApplicationStatus ? $state : ApplicationStatus::tryFrom((string) $state))?->label() ?? (string) $state)
                                ->color(fn (mixed $state): string => self::statusColor($state))
                                ->sortable(),
                            TextColumn::make('submitted_at')
                                ->label('Reçu le')
                                ->dateTime('d/m/Y à H:i')
                                ->icon(Heroicon::OutlinedCalendarDays)
                                ->iconColor('gray')
                                ->color('gray')
                                ->size(TextSize::ExtraSmall)
                                ->sortable(),
                        ])
                            ->alignment(Alignment::End)
                            ->space(1)
                            ->grow(false),
                    ])->from('sm'),
                    TableGrid::make([
                        'sm' => 2,
                    ])
                        ->schema([
                            Stack::make([
                                TextColumn::make('academic_orientation')
                                    ->label('Orientation')
                                    ->state(fn (Application $record): string => collect([
                                        $record->academicLevel?->code,
                                        $record->mention?->nom ?? $record->program?->title,
                                    ])->filter()->implode(' · '))
                                    ->icon(Heroicon::OutlinedAcademicCap)
                                    ->iconColor('warning')
                                    ->weight(FontWeight::SemiBold)
                                    ->wrap(),
                                TextColumn::make('parcours.nom')
                                    ->label('Parcours')
                                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Parcours : '.$state : 'Parcours non renseigné')
                                    ->color('gray')
                                    ->size(TextSize::Small),
                            ])->space(1),
                            Stack::make([
                                TextColumn::make('phone')
                                    ->label('Téléphone')
                                    ->icon(Heroicon::OutlinedPhone)
                                    ->iconColor('primary')
                                    ->searchable(),
                                TextColumn::make('campaign.title')
                                    ->label('Campagne')
                                    ->icon(Heroicon::OutlinedBuildingLibrary)
                                    ->iconColor('gray')
                                    ->color('gray')
                                    ->size(TextSize::Small)
                                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Campagne non renseignée')
                                    ->wrap(),
                            ])->space(1),
                        ])
                        ->extraAttributes(['class' => 'edsp-application-card-details']),
                ])->space(3),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut du dossier')
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
                    ->relationship('program', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('academic_level_id')
                    ->label('Niveau')
                    ->relationship('academicLevel', 'nom')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('mention_id')
                    ->label('Mention')
                    ->relationship('mention', 'nom')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('parcours_id')
                    ->label('Parcours')
                    ->relationship('parcours', 'nom')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('admission_campaign_id')
                    ->label('Campagne')
                    ->relationship('campaign', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActionsAlignment('end')
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Consulter le dossier')
                    ->url(fn (Application $record): string => self::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Traiter le dossier')
                    ->url(fn (Application $record): string => self::getUrl('edit', ['record' => $record])),
            ])
            ->headerActions([
                Action::make('grid_view')
                    ->label('Grille')
                    ->icon(Heroicon::OutlinedRectangleGroup)
                    ->color(fn (ManageApplications $livewire): string => $livewire->viewMode === 'grid' ? 'primary' : 'gray')
                    ->tooltip('Afficher les dossiers en grille')
                    ->action(fn (ManageApplications $livewire) => $livewire->setViewMode('grid')),
                Action::make('list_view')
                    ->label('Liste')
                    ->icon(Heroicon::OutlinedListBullet)
                    ->color(fn (ManageApplications $livewire): string => $livewire->viewMode === 'list' ? 'primary' : 'gray')
                    ->tooltip('Afficher les dossiers en liste')
                    ->action(fn (ManageApplications $livewire) => $livewire->setViewMode('list')),
                ExportAction::make()
                    ->label('Exporter les dossiers')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->authorize(fn (): bool => auth()->user()?->can('exportAny', Application::class) ?? false)
                    ->exporter(ApplicationExporter::class)
                    ->fileName(fn (): string => 'inscriptions-edsp-'.now()->format('Y-m-d-His')),
            ])
            ->emptyStateIcon(Heroicon::OutlinedInbox)
            ->emptyStateHeading('Aucun dossier d’inscription')
            ->emptyStateDescription('Les nouveaux dossiers apparaîtront ici dès leur envoi depuis le site public.');
    }

    private static function statusValue(mixed $state): string
    {
        return $state instanceof ApplicationStatus ? $state->value : (string) $state;
    }

    private static function statusColor(mixed $state): string
    {
        return match (self::statusValue($state)) {
            'submitted' => 'info',
            'under_review', 'waitlisted' => 'warning',
            'incomplete', 'rejected' => 'danger',
            'eligible', 'accepted' => 'success',
            default => 'gray',
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageApplications::route('/'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
