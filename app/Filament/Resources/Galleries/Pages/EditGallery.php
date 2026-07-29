<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditGallery extends EditRecord
{
    use TracksContentRevisions;

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
        return 'Modifier : '.$this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux galeries')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(GalleryResource::getUrl('index')),
            Action::make('publicPage')
                ->label('Voir sur le site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url('/galerie')
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === 'published'),
            DeleteAction::make()
                ->label('Supprimer'),
        ];
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingImageUploads = GalleryResource::pullNewImageUploads($data);

        return GalleryResource::applySimplifiedFormData($data, $this->getRecord());
    }

    protected function afterSave(): void
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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Galerie mise à jour';
    }
}
