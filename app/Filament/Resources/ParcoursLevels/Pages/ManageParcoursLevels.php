<?php

namespace App\Filament\Resources\ParcoursLevels\Pages;

use App\Filament\Resources\ParcoursLevels\ParcoursLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageParcoursLevels extends ManageRecords
{
    protected static string $resource = ParcoursLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
