<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

final class ActivityLogger
{
    /** @param array<string, mixed> $metadata */
    public function record(string $action, ?Model $subject = null, ?int $userId = null, array $metadata = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => $userId,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'metadata' => $metadata ?: null,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
