<?php

namespace App\Observers;

use App\Models\ContentRevision;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ContentLifecycleObserver
{
    public function created(Model $model): void
    {
        ContentRevision::query()->create([
            'revisionable_type' => $model::class,
            'revisionable_id' => $model->getKey(),
            'old_values' => null,
            'new_values' => $model->getAttributes(),
            'user_id' => auth()->id(),
            'action' => 'created',
        ]);
        app(ActivityLogger::class)->record('content.created', $model, auth()->id());
    }

    public function deleting(Model $model): void
    {
        $forceDeleting = method_exists($model, 'isForceDeleting') && $model->isForceDeleting();
        $action = $forceDeleting ? 'force_deleted' : 'deleted';

        ContentRevision::query()->create([
            'revisionable_type' => $model::class,
            'revisionable_id' => $model->getKey(),
            'old_values' => $model->getAttributes(),
            'new_values' => ['deleted' => true],
            'user_id' => auth()->id(),
            'action' => $action,
        ]);
        app(ActivityLogger::class)->record('content.'.$action, $model, auth()->id());
    }

    public function restored(Model $model): void
    {
        ContentRevision::query()->create([
            'revisionable_type' => $model::class,
            'revisionable_id' => $model->getKey(),
            'old_values' => ['deleted_at' => $model->getRawOriginal('deleted_at')],
            'new_values' => ['deleted_at' => null],
            'user_id' => auth()->id(),
            'action' => 'restored',
        ]);
        app(ActivityLogger::class)->record('content.restored', $model, auth()->id());
    }
}
