<?php

namespace App\Actions;

use App\Models\ContentRevision;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class RestoreRevision
{
    public function __construct(private readonly ActivityLogger $activities) {}

    public function handle(ContentRevision $revision, ?int $userId = null): Model
    {
        if ($userId !== null) {
            Gate::forUser(User::query()->findOrFail($userId))->authorize('restore', $revision);
        }

        $model = $revision->revisionable;
        abort_if($model === null, 404, 'Le contenu associé à cette révision n’existe plus.');

        $restoredValues = Arr::except($revision->old_values ?? [], ['id', 'created_at', 'updated_at', 'deleted_at']);
        $currentValues = $model->only(array_keys($restoredValues));

        return DB::transaction(function () use ($model, $restoredValues, $currentValues, $revision, $userId): Model {
            $model->update($restoredValues);
            ContentRevision::query()->create([
                'revisionable_type' => $model::class,
                'revisionable_id' => $model->getKey(),
                'old_values' => $currentValues,
                'new_values' => $model->fresh()->only(array_keys($restoredValues)),
                'user_id' => $userId,
                'action' => 'restored',
            ]);
            $this->activities->record('content.restored', $model, $userId, ['revision_id' => $revision->id]);

            return $model->fresh();
        });
    }
}
