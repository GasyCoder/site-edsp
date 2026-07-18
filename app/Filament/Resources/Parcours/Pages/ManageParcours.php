<?php

namespace App\Filament\Resources\Parcours\Pages;

use App\Filament\Resources\Parcours\ParcoursResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageParcours extends ManageRecords
{
    protected static string $resource = ParcoursResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
