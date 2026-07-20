<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Models\Mention;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePrograms extends ManageRecords
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Créer une page de mention')
                ->visible(fn (): bool => Mention::query()
                    ->where('is_active', true)
                    ->whereDoesntHave('programs')
                    ->exists())
                ->url(ProgramResource::getUrl('create')),
        ];
    }
}
