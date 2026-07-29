<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaVariantDispatcher;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MediaResource::batchUploadAction(),
            CreateAction::make()
                ->label('Ajouter un fichier')
                ->modalWidth('5xl')
                ->mutateDataUsing(fn (array $data): array => MediaResource::withStoredFileMetadata($data))
                ->after(function (Media $record): void {
                    app(ActivityLogger::class)->record(
                        'media.uploaded',
                        $record,
                        auth()->id(),
                        ['mime_type' => $record->mime_type, 'size' => $record->size],
                    );

                    if (str_starts_with((string) $record->mime_type, 'image/')) {
                        app(MediaVariantDispatcher::class)->dispatch($record);
                    }
                }),
        ];
    }
}
