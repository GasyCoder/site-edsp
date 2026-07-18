<?php

namespace App\Actions;

use App\Models\User;
use App\Services\ContentRevisionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class ArchiveContent
{
    public function __construct(private readonly ContentRevisionService $revisions) {}

    public function handle(Model $model, ?int $userId = null): Model
    {
        if ($userId !== null) {
            Gate::forUser(User::query()->findOrFail($userId))->authorize('publish', $model);
        }

        return $this->revisions->update($model, ['status' => 'archived'], $userId, 'archived');
    }
}
