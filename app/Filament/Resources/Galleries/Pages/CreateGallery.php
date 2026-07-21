<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Créer une galerie';
    }

    public function getSubheading(): string
    {
        return 'Donnez un titre, ajoutez des images de la médiathèque, publiez — c’est tout.';
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
        return GalleryResource::applySimplifiedFormData($data);
    }

    protected function afterCreate(): void
    {
        GalleryResource::syncDefaultCover($this->getRecord());
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
