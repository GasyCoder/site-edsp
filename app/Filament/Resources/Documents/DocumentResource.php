<?php

namespace App\Filament\Resources\Documents;

use App\Filament\Resources\Documents\Pages\ManageDocuments;
use App\Models\Document;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class DocumentResource extends Resource
{
    /** @var array<string, list<string>> */
    public const DOCUMENT_MIME_TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'odt' => ['application/vnd.oasis.opendocument.text'],
        'ods' => ['application/vnd.oasis.opendocument.spreadsheet'],
        'odp' => ['application/vnd.oasis.opendocument.presentation'],
    ];

    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Médiathèque';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $modelLabel = 'document';

    protected static ?string $pluralModelLabel = 'documents';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('category')
                            ->label('Catégorie')
                            ->datalist([
                                'Admissions',
                                'Formations',
                                'Règlements',
                                'Recherche',
                                'Vie étudiante',
                            ])
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                        Select::make('disk')
                            ->label('Stockage')
                            ->options([
                                'private' => 'Privé — servi par contrôle d’accès',
                            ])
                            ->default('private')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        FileUpload::make('path')
                            ->label('Fichier')
                            ->disk('private')
                            ->directory(fn (): string => 'documents/'.now()->format('Y/m'))
                            ->visibility('private')
                            ->acceptedFileTypes(self::allowedMimeTypes())
                            ->rules([fn (): Closure => self::strictUploadRule()])
                            ->maxSize(20 * 1024)
                            ->storeFileNamesIn('original_name')
                            ->downloadable()
                            ->openable()
                            ->preventFilePathTampering()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options(self::statusOptions())
                                ->default('draft')
                                ->required(),
                            DateTimePicker::make('published_at')
                                ->label('Date de publication')
                                ->seconds(false),
                            TextInput::make('position')
                                ->label('Ordre d’affichage')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            Toggle::make('is_public')
                                ->label('Téléchargement public')
                                ->helperText('Le document doit aussi être publié pour être accessible.')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('position')
            ->columns([
                TextColumn::make('title')
                    ->label('Document')
                    ->description(fn (Document $record): string => $record->original_name)
                    ->searchable(['title', 'original_name', 'description'])
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->placeholder('Sans catégorie')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::statusOptions()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'archived' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('size')
                    ->label('Taille')
                    ->formatStateUsing(fn (?int $state): string => Number::fileSize((int) $state))
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publication')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non planifiée')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('uploadedBy.name')
                    ->label('Ajouté par')
                    ->placeholder('Système')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statusOptions()),
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(fn (): array => Document::query()
                        ->whereNotNull('category')
                        ->where('category', '!=', '')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
                TernaryFilter::make('is_public')
                    ->label('Accès public'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('download')
                    ->label('Télécharger')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->iconButton()
                    ->tooltip('Télécharger')
                    ->url(fn (Document $record): string => route('documents.download', $record))
                    ->openUrlInNewTab(),
                self::editAction(),
                DeleteAction::make()
                    ->requiresConfirmation(),
                self::forceDeleteAction(),
                RestoreAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    self::forceDeleteBulkAction(),
                    RestoreBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withStoredFileMetadata(array $data, ?Document $record = null): array
    {
        $disk = (string) ($data['disk'] ?? $record?->disk ?? 'private');
        $path = (string) ($data['path'] ?? $record?->path ?? '');

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            throw ValidationException::withMessages([
                'path' => 'Le fichier envoyé est introuvable. Veuillez recommencer le téléversement.',
            ]);
        }

        $mimeType = (string) Storage::disk($disk)->mimeType($path);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if (! isset(self::DOCUMENT_MIME_TYPES[$extension]) || ! in_array($mimeType, self::DOCUMENT_MIME_TYPES[$extension], true)) {
            if ($record?->path !== $path || $record?->disk !== $disk) {
                Storage::disk($disk)->delete($path);
            }

            throw ValidationException::withMessages([
                'path' => 'Le type réel du document ne correspond pas à une extension autorisée.',
            ]);
        }

        if ($disk !== 'private') {
            $contents = Storage::disk($disk)->get($path);

            if (! Storage::disk('private')->put($path, $contents)) {
                throw ValidationException::withMessages([
                    'path' => 'Le document n’a pas pu être déplacé vers le stockage privé.',
                ]);
            }

            $disk = 'private';
        }

        $data['disk'] = $disk;
        $data['original_name'] = Str::limit(
            basename((string) ($data['original_name'] ?? $record?->original_name ?? basename($path))),
            255,
            '',
        );
        $data['mime_type'] = $mimeType;
        $data['size'] = Storage::disk($disk)->size($path);
        $data['uploaded_by'] = $record?->uploaded_by ?? auth()->id();

        return $data;
    }

    /** @return list<string> */
    public static function allowedMimeTypes(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::DOCUMENT_MIME_TYPES))));
    }

    private static function strictUploadRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value instanceof TemporaryUploadedFile) {
                return;
            }

            $extension = Str::lower($value->getClientOriginalExtension());
            $mimeType = (string) $value->getMimeType();

            if (! isset(self::DOCUMENT_MIME_TYPES[$extension]) || ! in_array($mimeType, self::DOCUMENT_MIME_TYPES[$extension], true)) {
                $fail('Le type réel du document ne correspond pas à son extension.');
            }
        };
    }

    private static function editAction(): EditAction
    {
        $previousFile = null;

        return EditAction::make()
            ->modalWidth('6xl')
            ->mutateRecordDataUsing(function (array $data, Document $record): array {
                $data['disk'] = $record->disk;
                $data['path'] = $record->path;

                return $data;
            })
            ->mutateDataUsing(fn (array $data, Document $record): array => self::withStoredFileMetadata($data, $record))
            ->before(function (Document $record) use (&$previousFile): void {
                $previousFile = ['disk' => $record->disk, 'path' => $record->path];
            })
            ->after(function (Document $record) use (&$previousFile): void {
                if (! $previousFile || ($previousFile['disk'] === $record->disk && $previousFile['path'] === $record->path)) {
                    return;
                }

                DB::afterCommit(fn () => Storage::disk($previousFile['disk'])->delete($previousFile['path']));
            });
    }

    private static function forceDeleteAction(): ForceDeleteAction
    {
        return ForceDeleteAction::make()
            ->requiresConfirmation()
            ->after(fn (Document $record) => DB::afterCommit(
                fn () => Storage::disk($record->disk)->delete($record->path),
            ));
    }

    private static function forceDeleteBulkAction(): ForceDeleteBulkAction
    {
        $files = [];

        return ForceDeleteBulkAction::make()
            ->requiresConfirmation()
            ->before(function (Collection $records) use (&$files): void {
                $files = $records
                    ->map(fn (Document $record): array => ['disk' => $record->disk, 'path' => $record->path])
                    ->all();
            })
            ->after(function () use (&$files): void {
                DB::afterCommit(function () use (&$files): void {
                    foreach ($files as $file) {
                        Storage::disk($file['disk'])->delete($file['path']);
                    }
                });
            });
    }

    /** @return array<string, string> */
    private static function statusOptions(): array
    {
        return [
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'archived' => 'Archivé',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDocuments::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
