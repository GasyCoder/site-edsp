<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\News\NewsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ManageNews extends ManageRecords
{
    use TracksContentRevisions;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle actualité')
                ->icon(Heroicon::OutlinedPlus)
                ->modalHeading('Créer une actualité')
                ->modalDescription('Rédigez l’article, configurez sa publication puis complétez le référencement si nécessaire.')
                ->modalWidth(Width::ScreenExtraLarge)
                ->stickyModalHeader()
                ->stickyModalFooter(),
        ];
    }
}
