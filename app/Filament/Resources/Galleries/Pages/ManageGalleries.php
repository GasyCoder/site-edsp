<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGalleries extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('7xl'),
        ];
    }
}
