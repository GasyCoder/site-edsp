<?php

namespace App\Filament\Resources\ExamSessions\Pages;

use App\Filament\Resources\ExamSessions\ExamSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageExamSessions extends ManageRecords
{
    protected static string $resource = ExamSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
