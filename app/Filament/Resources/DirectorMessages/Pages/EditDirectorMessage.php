<?php

namespace App\Filament\Resources\DirectorMessages\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\DirectorMessages\DirectorMessageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditDirectorMessage extends EditRecord
{
    use TracksContentRevisions;

    protected static string $resource = DirectorMessageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Modifier le mot du directeur';
    }

    public function getSubheading(): string
    {
        return 'La photo choisie est utilisée automatiquement sur l’accueil et sur la page Présentation.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publicPage')
                ->label('Voir la page publique')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url('/presentation')
                ->openUrlInNewTab(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Mot du directeur mis à jour';
    }
}
