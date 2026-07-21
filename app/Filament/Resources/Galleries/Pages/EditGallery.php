<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditGallery extends EditRecord
{
    use TracksContentRevisions;

    protected static string $resource = GalleryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

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
        return GalleryResource::applySimplifiedFormData($data, $this->getRecord());
    }

    protected function afterSave(): void
    {
        GalleryResource::syncDefaultCover($this->getRecord());
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Galerie mise à jour';
    }
}
