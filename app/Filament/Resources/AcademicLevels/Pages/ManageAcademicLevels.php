<?php

namespace App\Filament\Resources\AcademicLevels\Pages;

use App\Filament\Resources\AcademicLevels\AcademicLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAcademicLevels extends ManageRecords
{
    protected static string $resource = AcademicLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
