<?php

namespace App\Filament\Resources\ContentRevisions\Pages;

use App\Filament\Resources\ContentRevisions\ContentRevisionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageContentRevisions extends ManageRecords
{
    protected static string $resource = ContentRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
