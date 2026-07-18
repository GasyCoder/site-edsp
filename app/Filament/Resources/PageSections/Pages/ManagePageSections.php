<?php

namespace App\Filament\Resources\PageSections\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\PageSections\PageSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePageSections extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = PageSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
