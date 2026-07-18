<?php

namespace App\Services;

use App\Actions\RestoreRevision;
use App\Models\ContentRevision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class ContentRevisionService
{
    public function __construct(private readonly ActivityLogger $activities) {}

    public function update(Model $model, array $values, ?int $userId = null, string $action = 'updated'): Model
    {
        return DB::transaction(function () use ($model, $values, $userId, $action) {
            $old = $model->only(array_keys($values));
            $model->update($values);
            ContentRevision::create(['revisionable_type' => $model::class, 'revisionable_id' => $model->getKey(), 'old_values' => $old, 'new_values' => $model->fresh()->only(array_keys($values)), 'user_id' => $userId, 'action' => $action]);
            $this->activities->record('content.'.$action, $model, $userId);

            return $model->fresh();
        });
    }

    public function restore(ContentRevision $revision, ?int $userId = null): Model
    {
        return app(RestoreRevision::class)->handle($revision, $userId);
    }
}
