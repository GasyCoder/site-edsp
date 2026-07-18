<?php

namespace App\Observers;

use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\Model;

class ContentSlugObserver
{
    public function updating(Model $model): void
    {
        if (! $model->isDirty('slug') || blank($model->getOriginal('slug'))) {
            return;
        }

        $oldPath = $this->path($model, (string) $model->getOriginal('slug'));
        $newPath = $this->path($model, (string) $model->getAttribute('slug'));

        if ($oldPath === $newPath) {
            return;
        }

        Redirect::query()->where('source_path', $newPath)->delete();
        Redirect::query()->where('destination_url', $oldPath)->where('source_path', '!=', $newPath)->update(['destination_url' => $newPath]);
        Redirect::query()->updateOrCreate(
            ['source_path' => $oldPath],
            ['destination_url' => $newPath, 'status_code' => 301, 'is_active' => true],
        );
    }

    private function path(Model $model, string $slug): string
    {
        return match (true) {
            $model instanceof News => '/actualites/'.$slug,
            $model instanceof Program => '/formations/'.$slug,
            $model instanceof Page => '/'.$slug,
            default => '/'.$slug,
        };
    }
}
