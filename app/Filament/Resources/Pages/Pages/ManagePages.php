<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePages extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
