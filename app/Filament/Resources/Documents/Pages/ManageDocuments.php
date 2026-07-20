<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageDocuments extends ManageRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publicDocuments')
                ->label('Voir les documents sur le site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('documents.index'))
                ->openUrlInNewTab(),
            CreateAction::make()
                ->modalWidth('6xl')
                ->mutateDataUsing(fn (array $data): array => DocumentResource::withStoredFileMetadata($data)),
        ];
    }
}
