<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePrograms extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
