<?php

namespace App\Actions;

use App\Models\User;
use App\Services\ContentRevisionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class PublishContent
{
    public function __construct(private readonly ContentRevisionService $revisions) {}

    public function handle(Model $model, ?int $userId = null): Model
    {
        if ($userId !== null) {
            Gate::forUser(User::query()->findOrFail($userId))->authorize('publish', $model);
        }

        $values = ['status' => 'published', 'published_at' => now()];
        if ($userId && array_key_exists('approved_by', $model->getAttributes())) {
            $values['approved_by'] = $userId;
        }

        return $this->revisions->update($model, $values, $userId, 'published');
    }
}
