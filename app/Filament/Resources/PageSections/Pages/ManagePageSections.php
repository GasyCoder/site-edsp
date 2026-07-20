<?php

namespace App\Filament\Resources\PageSections\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\PageSections\PageSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManagePageSections extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = PageSectionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Créer une section de page')
                ->modalWidth('7xl'),
        ];
    }
}
