<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Créer une page de mention';
    }

    public function getSubheading(): string
    {
        return 'Présentez une mention sur le site. Ses parcours et niveaux sont repris automatiquement depuis Scolarité.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux formations')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(ProgramResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ProgramResource::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Formation créée';
    }
}
