<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManagePages extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = PageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Créer une page')
                ->modalWidth('7xl'),
        ];
    }
}
