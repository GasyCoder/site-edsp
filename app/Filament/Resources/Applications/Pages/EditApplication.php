<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Actions\ChangeApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public static bool $formActionsAreSticky = true;

    public function getHeading(): string
    {
        return 'Traiter le dossier '.$this->getRecord()->application_number;
    }

    public function getSubheading(): string
    {
        return 'Modifiez le statut ou ajoutez une note interne, puis enregistrez les changements.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour aux dossiers')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(ApplicationResource::getUrl('index')),
            ViewAction::make()
                ->label('Voir la fiche complète')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => ApplicationResource::getUrl('view', ['record' => $this->getRecord()])),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['internal_notes'] = $this->getRecord()->internal_notes;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = (string) $record->getRawOriginal('status');
        $newStatus = (string) ($data['status'] ?? $oldStatus);
        $internalNotes = $data['internal_notes'] ?? null;
        $notesChanged = $record->getAttribute('internal_notes') !== $internalNotes;

        $record->update(['internal_notes' => $internalNotes]);

        if ($notesChanged) {
            app(ActivityLogger::class)->record('application.notes_updated', $record, auth()->id());
        }

        if ($newStatus !== $oldStatus) {
            return app(ChangeApplicationStatus::class)->handle(
                $record,
                ApplicationStatus::from($newStatus),
                auth()->id(),
                $internalNotes,
            );
        }

        return $record->fresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Dossier mis à jour';
    }
}
