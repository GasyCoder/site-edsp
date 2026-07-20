<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Concerns\TracksContentRevisions;
use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditProgram extends EditRecord
{
    use TracksContentRevisions;

    protected static string $resource = ProgramResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Modifier : '.$this->getRecord()->title;
    }

    public function getSubheading(): string
    {
        return 'Le contenu éditorial est modifiable ici ; les parcours et niveaux restent synchronisés avec Scolarité.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux formations')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(ProgramResource::getUrl('index')),
            Action::make('publicPage')
                ->label('Voir sur le site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('programs.show', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === 'published'),
            DeleteAction::make()
                ->label('Supprimer'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Formation mise à jour';
    }
}
