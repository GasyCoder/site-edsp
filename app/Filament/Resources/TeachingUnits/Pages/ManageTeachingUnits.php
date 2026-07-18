<?php

namespace App\Filament\Resources\TeachingUnits\Pages;

use App\Filament\Resources\TeachingUnits\TeachingUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTeachingUnits extends ManageRecords
{
    protected static string $resource = TeachingUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
