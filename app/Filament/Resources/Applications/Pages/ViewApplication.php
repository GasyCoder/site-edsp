<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        app(ActivityLogger::class)->record(
            'application.consulted',
            $this->getRecord(),
            auth()->id(),
        );
    }

    public function getHeading(): string
    {
        return 'Dossier '.$this->getRecord()->application_number;
    }

    public function getSubheading(): string
    {
        return trim($this->getRecord()->last_name.' '.$this->getRecord()->first_name);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux dossiers')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(ApplicationResource::getUrl('index')),
            EditAction::make()
                ->label('Traiter le dossier')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->url(fn (): string => ApplicationResource::getUrl('edit', ['record' => $this->getRecord()])),
        ];
    }
}
