<?php

namespace App\Observers;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLifecycleObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored');
    }

    private function record(Model $model, string $event): void
    {
        $resource = Str::of(class_basename($model))->snake()->toString();
        app(ActivityLogger::class)->record($resource.'.'.$event, $model, auth()->id());
    }
}
