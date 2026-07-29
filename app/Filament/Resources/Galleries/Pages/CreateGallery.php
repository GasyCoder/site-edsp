<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    /** @var array{paths: list<string>, names: array<string, string>, alt_prefix: ?string, caption: ?string} */
    private array $pendingImageUploads = [
        'paths' => [],
        'names' => [],
        'alt_prefix' => null,
        'caption' => null,
    ];

    public function getHeading(): string
    {
        return 'Créer une galerie';
    }

    public function getSubheading(): string
    {
        return 'Donnez un titre, importez vos images ou choisissez-les dans la médiathèque, puis publiez.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux galeries')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(GalleryResource::getUrl('index')),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingImageUploads = GalleryResource::pullNewImageUploads($data);

        return GalleryResource::applySimplifiedFormData($data);
    }

    protected function afterCreate(): void
    {
        $count = GalleryResource::attachUploadedImages($this->getRecord(), $this->pendingImageUploads);
        GalleryResource::syncDefaultCover($this->getRecord());

        if ($count > 0) {
            Notification::make()
                ->success()
                ->title($count === 1 ? 'Une image ajoutée' : "{$count} images ajoutées")
                ->body('Les images ont également été enregistrées dans la médiathèque.')
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return GalleryResource::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Galerie créée';
    }
}
