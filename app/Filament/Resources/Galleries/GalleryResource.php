<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Forms\MediaImagePreview;
use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ManageGalleries;
use App\Filament\Resources\Media\MediaResource as MediaLibraryResource;
use App\Models\Gallery;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Médiathèque';

    protected static ?string $navigationLabel = 'Galeries';

    protected static ?string $modelLabel = 'galerie';

    protected static ?string $pluralModelLabel = 'galeries';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Galerie')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->helperText('Pré-rempli automatiquement — modifiez-le si vous voulez un nom plus parlant (ex. « Remise des diplômes 2026 »).')
                            ->default(fn (): string => 'Galerie du '.now()->translatedFormat('j F Y'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description (optionnelle)')
                            ->rows(3)
                            ->maxLength(3000),
                        Toggle::make('published')
                            ->label('Publier sur le site')
                            ->helperText('Désactivé, la galerie reste en brouillon, invisible pour les visiteurs.')
                            ->default(true)
                            ->dehydrated()
                            ->afterStateHydrated(fn (Toggle $component, ?Gallery $record) => $component->state(
                                $record === null || ($record->status === 'published' && $record->is_visible),
                            )),
                    ]),
                Section::make('Images de la galerie')
                    ->description('Importez plusieurs nouvelles photos ou choisissez des images déjà présentes dans la médiathèque.')
                    ->schema([
                        FileUpload::make('new_images')
                            ->label('Importer de nouvelles images')
                            ->helperText('Jusqu’à 30 images. Elles seront ajoutées à la médiathèque et directement reliées à cette galerie.')
                            ->disk('public')
                            ->directory(fn (): string => MediaLibraryResource::currentMediaDirectory())
                            ->visibility('public')
                            ->acceptedFileTypes(MediaLibraryResource::allowedImageMimeTypes())
                            ->rules([fn () => MediaLibraryResource::strictUploadRule()])
                            ->maxSize(MediaLibraryResource::effectiveUploadSizeInKilobytes())
                            ->maxFiles(30)
                            ->multiple()
                            ->reorderable()
                            ->storeFileNamesIn('new_image_names')
                            ->imagePreviewHeight('180')
                            ->panelLayout('grid')
                            ->previewable()
                            ->openable()
                            ->preventFilePathTampering()
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('new_images_alt_prefix')
                                ->label('Description commune (optionnelle)')
                                ->helperText('Le numéro de la photo sera ajouté. Par défaut, le titre de la galerie est utilisé.')
                                ->maxLength(180),
                            TextInput::make('new_images_caption')
                                ->label('Légende commune (optionnelle)')
                                ->maxLength(1000),
                        ]),
                        Repeater::make('images')
                            ->label('Images existantes de la médiathèque')
                            ->relationship()
                            ->orderColumn('position')
                            ->schema([
                                Select::make('media_id')
                                    ->label('Image')
                                    ->relationship(
                                        name: 'media',
                                        titleAttribute: 'original_name',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                    )
                                    ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaImagePreview::optionLabel($record))
                                    ->allowHtml()
                                    ->searchable()
                                    ->preload()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->required(),
                                TextInput::make('caption')
                                    ->label('Légende (optionnelle)')
                                    ->maxLength(1000),
                            ])
                            ->columns(2)
                            ->itemNumbers()
                            ->addActionLabel('Ajouter une image')
                            ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
                            ->columnSpanFull(),
                    ]),
                Section::make('Réglages avancés')
                    ->description('Couverture, ordre d’affichage et version anglaise. Tout est optionnel.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('cover_image_id')
                                ->label('Image de couverture')
                                ->helperText('Par défaut : la première image de la galerie.')
                                ->relationship(
                                    name: 'coverImage',
                                    titleAttribute: 'original_name',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('mime_type', 'like', 'image/%'),
                                )
                                ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaImagePreview::optionLabel($record))
                                ->allowHtml()
                                ->searchable()
                                ->preload(),
                            TextInput::make('position')
                                ->label('Ordre d’affichage')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                        TextInput::make('translations.en.title')->label('Titre en anglais')->maxLength(255),
                        Textarea::make('translations.en.description')->label('Description en anglais')->rows(3)->maxLength(3000),
                    ])
                    ->collapsed(),
            ]);
    }

    /**
     * Applique l'interrupteur « Publier » aux colonnes réelles et génère le slug.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applySimplifiedFormData(array $data, ?Gallery $record = null): array
    {
        unset(
            $data['new_images'],
            $data['new_image_names'],
            $data['new_images_alt_prefix'],
            $data['new_images_caption'],
        );

        $published = (bool) ($data['published'] ?? false);
        unset($data['published']);

        $data['status'] = $published ? 'published' : 'draft';
        $data['is_visible'] = $published;
        $data['published_at'] = $published ? ($record?->published_at ?? now()) : null;

        if ($record === null) {
            $data['slug'] = self::generateUniqueSlug((string) ($data['title'] ?? ''));
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{paths: list<string>, names: array<string, string>, alt_prefix: ?string, caption: ?string}
     */
    public static function pullNewImageUploads(array &$data): array
    {
        $uploads = [
            'paths' => array_values(array_filter(
                (array) ($data['new_images'] ?? []),
                fn (mixed $path): bool => is_string($path) && filled($path),
            )),
            'names' => (array) ($data['new_image_names'] ?? []),
            'alt_prefix' => filled($data['new_images_alt_prefix'] ?? null)
                ? trim((string) $data['new_images_alt_prefix'])
                : null,
            'caption' => filled($data['new_images_caption'] ?? null)
                ? trim((string) $data['new_images_caption'])
                : null,
        ];

        unset(
            $data['new_images'],
            $data['new_image_names'],
            $data['new_images_alt_prefix'],
            $data['new_images_caption'],
        );

        return $uploads;
    }

    /**
     * @param  array{paths: list<string>, names: array<string, string>, alt_prefix: ?string, caption: ?string}  $uploads
     */
    public static function attachUploadedImages(Gallery $gallery, array $uploads): int
    {
        if ($uploads['paths'] === []) {
            return 0;
        }

        $media = MediaLibraryResource::createManyFromStoredImages(
            $uploads['paths'],
            $uploads['names'],
            $uploads['alt_prefix'] ?: $gallery->title,
            $uploads['caption'],
        );
        $position = (int) ($gallery->images()->max('position') ?? 0);

        foreach ($media as $image) {
            $gallery->images()->create([
                'media_id' => $image->id,
                'title' => $image->alt_text,
                'alt_text' => $image->alt_text,
                'caption' => $image->caption,
                'position' => ++$position,
                'is_visible' => true,
            ]);
        }

        return $media->count();
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'galerie';
        $slug = $base;
        $suffix = 2;

        while (Gallery::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public static function syncDefaultCover(Gallery $record): void
    {
        if ($record->cover_image_id !== null) {
            return;
        }

        $firstImageId = $record->images()->orderBy('position')->value('media_id');

        if ($firstImageId !== null) {
            $record->forceFill(['cover_image_id' => $firstImageId])->saveQuietly();
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('position')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('coverImage'))
            ->columns([
                ImageColumn::make('cover_preview')
                    ->label('Couverture')
                    ->state(fn (Gallery $record): ?string => $record->coverImage?->thumbnail_url)
                    ->square()
                    ->size(54),
                TextColumn::make('title')
                    ->label('Galerie')
                    ->description(fn (Gallery $record): ?string => filled($record->description) ? Str::limit($record->description, 65) : null)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('images_count')
                    ->label('Images')
                    ->counts('images')
                    ->badge()
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
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean()
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
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statusOptions()),
                TernaryFilter::make('is_visible')
                    ->label('Visibilité'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Gallery $record): string => self::getUrl('edit', ['record' => $record])),
                DeleteAction::make()
                    ->requiresConfirmation(),
                ForceDeleteAction::make()
                    ->requiresConfirmation(),
                RestoreAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    RestoreBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
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
            'index' => ManageGalleries::route('/'),
            'create' => CreateGallery::route('/create'),
            'edit' => EditGallery::route('/{record}/edit'),
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
