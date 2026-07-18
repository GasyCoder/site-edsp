<?php

namespace App\Filament\Resources\CourseElements\Pages;

use App\Filament\Resources\CourseElements\CourseElementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCourseElements extends ManageRecords
{
    protected static string $resource = CourseElementResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
