<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageGalleries extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle galerie')
                ->icon(Heroicon::OutlinedPlus)
                ->url(GalleryResource::getUrl('create')),
        ];
    }
}
