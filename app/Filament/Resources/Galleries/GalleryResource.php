<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Forms\MediaImagePreview;
use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ManageGalleries;
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
                    ->description('Choisissez des images de la médiathèque et organisez-les par glisser-déposer. La légende est optionnelle.')
                    ->schema([
                        Repeater::make('images')
                            ->hiddenLabel()
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
