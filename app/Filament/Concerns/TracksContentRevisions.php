<?php

namespace App\Filament\Concerns;

use App\Services\ContentRevisionService;
use Illuminate\Database\Eloquent\Model;

trait TracksContentRevisions
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ContentRevisionService::class)->update($record, $data, auth()->id());
    }
}
