<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Actions\ChangeApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Services\ActivityLogger;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageApplications extends ManageRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
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
}
