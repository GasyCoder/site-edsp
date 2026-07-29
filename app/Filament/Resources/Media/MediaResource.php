<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\MediaService;
use App\Services\MediaVariantDispatcher;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;
use UnitEnum;

class MediaResource extends Resource
{
    private const MAX_UPLOAD_SIZE_KB = 10 * 1024;

    private const MAX_BATCH_IMAGES = 30;

    /** @var array<string, list<string>> */
    public const ALLOWED_MIME_TYPES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Médiathèque';

    protected static ?string $navigationLabel = 'Médias';

    protected static ?string $modelLabel = 'média';

    protected static ?string $pluralModelLabel = 'médias';

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fichier')
                    ->description(fn (): string => sprintf(
                        'Formats autorisés : JPEG, PNG, WebP, PDF, DOC ou DOCX — %s maximum.',
                        self::effectiveUploadSizeLabel(),
                    ))
                    ->schema([
                        FileUpload::make('path')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory(fn (): string => 'media/'.now()->format('Y/m'))
                            ->visibility('public')
                            ->acceptedFileTypes(self::allowedMimeTypes())
                            ->rules([fn (): Closure => self::strictUploadRule()])
                            ->maxSize(self::effectiveUploadSizeInKilobytes())
                            ->storeFileNamesIn('original_name')
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Get $get, Set $set): void {
                                if (filled($get('alt_text'))) {
                                    return;
                                }

                                $file = is_array($state) ? reset($state) : $state;

                                if (! $file instanceof TemporaryUploadedFile) {
                                    return;
                                }

                                $suggestion = self::altTextFromFilename($file->getClientOriginalName());

                                if ($suggestion !== '') {
                                    $set('alt_text', $suggestion);
                                }
                            })
                            ->imagePreviewHeight('260')
                            ->panelLayout('integrated')
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->preventFilePathTampering()
                            ->required()
                            ->columnSpanFull(),
                        Hidden::make('disk')
                            ->default('public'),
                    ]),
                Section::make('Accessibilité et légende')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('alt_text')
                                ->label('Texte alternatif')
                                ->helperText('Décrivez une image pour les lecteurs d’écran ; pour un document, indiquez son intitulé.')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('caption')
                                ->label('Légende')
                                ->rows(3)
                                ->maxLength(1000),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview')
                    ->label('Aperçu')
                    ->state(fn (Media $record): ?string => str_starts_with($record->mime_type, 'image/') ? $record->thumbnail_url : null)
                    ->square()
                    ->size(56),
                TextColumn::make('original_name')
                    ->label('Nom du fichier')
                    ->description(fn (Media $record): ?string => $record->alt_text)
                    ->icon(fn (Media $record): Heroicon => str_starts_with($record->mime_type, 'image/')
                        ? Heroicon::OutlinedPhoto
                        : Heroicon::OutlinedDocument)
                    ->searchable()
                    ->sortable()
                    ->limit(45),
                TextColumn::make('mime_type')
                    ->label('Format')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/webp' => 'WebP',
                        'application/pdf' => 'PDF',
                        'application/msword' => 'DOC',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
                        default => $state ?? '—',
                    }),
                TextColumn::make('dimensions')
                    ->label('Dimensions')
                    ->state(fn (Media $record): string => ($record->width && $record->height)
                        ? "{$record->width} × {$record->height} px"
                        : '—')
                    ->toggleable(),
                TextColumn::make('size')
                    ->label('Taille')
                    ->formatStateUsing(fn (?int $state): string => Number::fileSize((int) $state))
                    ->sortable(),
                TextColumn::make('uploadedBy.name')
                    ->label('Ajoutée par')
                    ->placeholder('Système')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Ajoutée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('Type de média')
                    ->options([
                        'image' => 'Images',
                        'document' => 'Documents',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'image' => $query->where('mime_type', 'like', 'image/%'),
                            'document' => $query->where('mime_type', 'not like', 'image/%'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                self::editAction(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription(fn (): string => self::isSuperAdmin()
                        ? 'En tant que super administrateur, ce média sera aussi retiré de tous les contenus qui l’utilisent (pages, actualités, galeries, réglages…).'
                        : 'Êtes-vous sûr de vouloir faire cela ?')
                    ->using(function (Media $record, DeleteAction $action): bool {
                        try {
                            app(MediaService::class)->delete($record, auth()->id(), force: self::isSuperAdmin());

                            return true;
                        } catch (ValidationException $exception) {
                            $action->failureNotificationTitle(
                                collect($exception->errors())->flatten()->first()
                                    ?? 'Ce média ne peut pas être supprimé.',
                            );

                            return false;
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription(fn (): string => self::isSuperAdmin()
                            ? 'En tant que super administrateur, ces médias seront aussi retirés de tous les contenus qui les utilisent (pages, actualités, galeries, réglages…).'
                            : 'Êtes-vous sûr de vouloir faire cela ?')
                        ->action(function (EloquentCollection $records, DeleteBulkAction $action): void {
                            $service = app(MediaService::class);
                            $force = self::isSuperAdmin();
                            $deleted = 0;
                            $blocked = [];

                            foreach ($records as $record) {
                                try {
                                    $service->delete($record, auth()->id(), force: $force);
                                    $deleted++;
                                } catch (ValidationException) {
                                    $blocked[] = $record->original_name;
                                }
                            }

                            if ($deleted > 0) {
                                Notification::make()
                                    ->success()
                                    ->title($deleted === 1 ? 'Un média supprimé' : "{$deleted} médias supprimés")
                                    ->send();
                            }

                            if ($blocked !== []) {
                                Notification::make()
                                    ->danger()
                                    ->title('Médias encore utilisés')
                                    ->body('Impossible de supprimer : '.implode(', ', $blocked).'. Retirez-les d’abord des contenus qui les utilisent.')
                                    ->persistent()
                                    ->send();
                            }

                            $action->deselectRecordsAfterCompletion();
                        }),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withStoredFileMetadata(array $data, ?Media $record = null): array
    {
        $disk = (string) ($data['disk'] ?? $record?->disk ?? 'public');
        $path = (string) ($data['path'] ?? $record?->path ?? '');

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            throw ValidationException::withMessages([
                'path' => 'Le fichier envoyé est introuvable. Veuillez recommencer le téléversement.',
            ]);
        }

        $mimeType = (string) Storage::disk($disk)->mimeType($path);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if (! isset(self::ALLOWED_MIME_TYPES[$extension]) || ! in_array($mimeType, self::ALLOWED_MIME_TYPES[$extension], true)) {
            if ($record?->path !== $path || $record?->disk !== $disk) {
                Storage::disk($disk)->delete($path);
            }

            throw ValidationException::withMessages([
                'path' => 'Le type réel du fichier ne correspond pas à une extension autorisée.',
            ]);
        }

        if (str_starts_with($mimeType, 'image/') && blank($data['alt_text'] ?? $record?->alt_text)) {
            throw ValidationException::withMessages([
                'alt_text' => 'Un texte alternatif est requis pour une image.',
            ]);
        }

        $data['disk'] = $disk;
        $data['filename'] = basename($path);
        $data['original_name'] = Str::limit(
            basename((string) ($data['original_name'] ?? $record?->original_name ?? basename($path))),
            255,
            '',
        );
        $data['mime_type'] = $mimeType;
        $data['extension'] = $extension;
        $data['size'] = Storage::disk($disk)->size($path);
        $data['uploaded_by'] = $record?->uploaded_by ?? auth()->id();
        $data['width'] = null;
        $data['height'] = null;

        if (str_starts_with($mimeType, 'image/')) {
            try {
                $dimensions = getimagesize(Storage::disk($disk)->path($path));

                if ($dimensions !== false) {
                    [$data['width'], $data['height']] = $dimensions;
                }
            } catch (Throwable) {
                // Les dimensions restent facultatives pour les pilotes distants.
            }
        }

        return $data;
    }

    public static function batchUploadAction(): Action
    {
        return Action::make('uploadMultiple')
            ->label('Importer plusieurs images')
            ->icon(Heroicon::OutlinedPhoto)
            ->color('success')
            ->modalHeading('Importer plusieurs images')
            ->modalDescription('Sélectionnez jusqu’à 30 images. Chaque fichier sera ajouté séparément à la médiathèque avec un aperçu et un nom accessible.')
            ->modalWidth('6xl')
            ->schema([
                Section::make('Images')
                    ->description(sprintf(
                        'JPEG, PNG ou WebP. %s maximum par image.',
                        self::effectiveUploadSizeLabel(),
                    ))
                    ->schema([
                        FileUpload::make('files')
                            ->label('Images à importer')
                            ->disk('public')
                            ->directory(fn (): string => self::currentMediaDirectory())
                            ->visibility('public')
                            ->acceptedFileTypes(self::allowedImageMimeTypes())
                            ->rules([fn (): Closure => self::strictUploadRule()])
                            ->maxSize(self::effectiveUploadSizeInKilobytes())
                            ->maxFiles(self::MAX_BATCH_IMAGES)
                            ->multiple()
                            ->reorderable()
                            ->storeFileNamesIn('original_names')
                            ->imagePreviewHeight('180')
                            ->panelLayout('grid')
                            ->previewable()
                            ->openable()
                            ->preventFilePathTampering()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('alt_prefix')
                            ->label('Préfixe du texte alternatif (optionnel)')
                            ->helperText('Exemple : « Journée d’accueil ». Le numéro de chaque image sera ajouté automatiquement. Sans préfixe, le nom du fichier sera utilisé.')
                            ->maxLength(180),
                        Textarea::make('caption')
                            ->label('Légende commune (optionnelle)')
                            ->rows(2)
                            ->maxLength(1000),
                    ]),
            ])
            ->action(function (array $data): void {
                $media = self::createManyFromStoredImages(
                    (array) ($data['files'] ?? []),
                    (array) ($data['original_names'] ?? []),
                    $data['alt_prefix'] ?? null,
                    $data['caption'] ?? null,
                );
                $count = $media->count();

                Notification::make()
                    ->success()
                    ->title($count === 1 ? 'Une image importée' : "{$count} images importées")
                    ->body('Les images sont disponibles dans la médiathèque.')
                    ->send();
            })
            ->visible(fn (): bool => self::canCreate());
    }

    /**
     * @param  list<string>  $paths
     * @param  array<string, string>  $originalNames
     * @return Collection<int, Media>
     */
    public static function createManyFromStoredImages(
        array $paths,
        array $originalNames = [],
        ?string $altPrefix = null,
        ?string $caption = null,
    ): Collection {
        $paths = collect($paths)
            ->filter(fn (mixed $path): bool => is_string($path) && filled($path))
            ->unique()
            ->values();

        if ($paths->isEmpty()) {
            throw ValidationException::withMessages([
                'files' => 'Sélectionnez au moins une image à importer.',
            ]);
        }

        if ($paths->count() > self::MAX_BATCH_IMAGES) {
            throw ValidationException::withMessages([
                'files' => 'Vous pouvez importer au maximum '.self::MAX_BATCH_IMAGES.' images à la fois.',
            ]);
        }

        $created = collect();

        try {
            $created = DB::transaction(function () use ($altPrefix, $caption, $originalNames, $paths): Collection {
                return $paths->map(function (string $path, int $index) use ($altPrefix, $caption, $originalNames): Media {
                    if (! self::isBatchUploadPath($path) || Media::query()->where('disk', 'public')->where('path', $path)->exists()) {
                        throw ValidationException::withMessages([
                            'files' => 'Une image envoyée est invalide ou existe déjà dans la médiathèque.',
                        ]);
                    }

                    $originalName = (string) ($originalNames[$path] ?? basename($path));
                    $altText = filled($altPrefix)
                        ? trim((string) $altPrefix).' '.($index + 1)
                        : self::altTextFromFilename($originalName);
                    $metadata = self::withStoredFileMetadata([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $originalName,
                        'alt_text' => $altText,
                        'caption' => $caption,
                    ]);

                    if (! str_starts_with((string) $metadata['mime_type'], 'image/')) {
                        throw ValidationException::withMessages([
                            'files' => 'Seuls les fichiers image peuvent être importés en lot.',
                        ]);
                    }

                    $media = Media::query()->create($metadata);
                    app(ActivityLogger::class)->record(
                        'media.uploaded',
                        $media,
                        auth()->id(),
                        ['mime_type' => $media->mime_type, 'size' => $media->size, 'batch' => true],
                    );

                    return $media;
                });
            });
        } catch (Throwable $exception) {
            // Ne supprime que les nouveaux fichiers générés par ce formulaire.
            // Un chemin déjà référencé par la médiathèque ne doit jamais être effacé.
            $deletablePaths = $paths
                ->filter(fn (string $path): bool => self::isBatchUploadPath($path))
                ->reject(fn (string $path): bool => Media::query()
                    ->where('disk', 'public')
                    ->where('path', $path)
                    ->exists())
                ->all();

            Storage::disk('public')->delete($deletablePaths);
            throw $exception;
        }

        $created->each(fn (Media $media) => app(MediaVariantDispatcher::class)->dispatch($media));

        return $created;
    }

    public static function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasRole('superadmin');
    }

    /** @return list<string> */
    public static function allowedMimeTypes(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::ALLOWED_MIME_TYPES))));
    }

    /** @return list<string> */
    public static function allowedImageMimeTypes(): array
    {
        return array_values(array_filter(
            self::allowedMimeTypes(),
            fn (string $mimeType): bool => str_starts_with($mimeType, 'image/'),
        ));
    }

    public static function altTextFromFilename(string $filename): string
    {
        $name = pathinfo(trim($filename), PATHINFO_FILENAME);
        $name = preg_replace('/[\s_\-]+/u', ' ', $name) ?? $name;

        return Str::ucfirst(trim($name));
    }

    public static function effectiveUploadSizeInKilobytes(): int
    {
        $limits = [self::MAX_UPLOAD_SIZE_KB];

        foreach (['upload_max_filesize', 'post_max_size'] as $setting) {
            $limit = self::phpIniSizeInKilobytes((string) ini_get($setting));

            if ($limit !== null) {
                $limits[] = $limit;
            }
        }

        return min($limits);
    }

    private static function effectiveUploadSizeLabel(): string
    {
        $kilobytes = self::effectiveUploadSizeInKilobytes();

        if ($kilobytes >= 1024 && $kilobytes % 1024 === 0) {
            return ($kilobytes / 1024).' Mo';
        }

        return $kilobytes.' Ko';
    }

    private static function phpIniSizeInKilobytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || (float) $value <= 0) {
            return null;
        }

        $unit = strtolower(substr($value, -1));
        $size = (float) $value;

        $bytes = match ($unit) {
            'g' => $size * 1024 ** 3,
            'm' => $size * 1024 ** 2,
            'k' => $size * 1024,
            default => $size,
        };

        return max(1, (int) floor($bytes / 1024));
    }

    public static function strictUploadRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value instanceof TemporaryUploadedFile) {
                return;
            }

            $extension = Str::lower($value->getClientOriginalExtension());
            $mimeType = (string) $value->getMimeType();

            if (! isset(self::ALLOWED_MIME_TYPES[$extension]) || ! in_array($mimeType, self::ALLOWED_MIME_TYPES[$extension], true)) {
                $fail('Le type réel du fichier ne correspond pas à son extension.');

                return;
            }

            if (str_starts_with($mimeType, 'image/')) {
                $dimensions = @getimagesize($value->getRealPath());

                if ($dimensions === false || $dimensions[0] > 12000 || $dimensions[1] > 12000 || ($dimensions[0] * $dimensions[1]) > 40_000_000) {
                    $fail('Les dimensions de cette image sont invalides ou excessives.');
                }
            }
        };
    }

    public static function currentMediaDirectory(): string
    {
        return 'media/'.now()->format('Y/m');
    }

    private static function isBatchUploadPath(string $path): bool
    {
        if (! Str::startsWith($path, self::currentMediaDirectory().'/')) {
            return false;
        }

        return preg_match(
            '/^[0-9A-HJKMNP-TV-Z]{26}\.(?:jpe?g|png|webp)$/i',
            basename($path),
        ) === 1;
    }

    private static function editAction(): EditAction
    {
        $previousFile = null;

        return EditAction::make()
            ->modalWidth('5xl')
            ->mutateDataUsing(fn (array $data, Media $record): array => self::withStoredFileMetadata($data, $record))
            ->before(function (Media $record) use (&$previousFile): void {
                $previousFile = ['disk' => $record->disk, 'path' => $record->path];
            })
            ->after(function (Media $record) use (&$previousFile): void {
                app(ActivityLogger::class)->record('media.updated', $record, auth()->id());

                if (str_starts_with((string) $record->mime_type, 'image/')) {
                    app(MediaVariantDispatcher::class)->dispatch($record);
                }

                if (! $previousFile || $previousFile['path'] === $record->path) {
                    return;
                }

                DB::afterCommit(fn () => Storage::disk($previousFile['disk'])->delete($previousFile['path']));
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMedia::route('/'),
        ];
    }
}
