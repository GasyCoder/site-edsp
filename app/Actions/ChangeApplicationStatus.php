<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Jobs\SendApplicationStatusNotification;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ChangeApplicationStatus
{
    public function __construct(private readonly ActivityLogger $activities) {}

    public function handle(Application $application, ApplicationStatus $status, ?int $userId, ?string $comment = null): Application
    {
        if ($userId !== null) {
            Gate::forUser(User::query()->findOrFail($userId))->authorize('changeStatus', $application);
        }

        $changed = false;

        $application = DB::transaction(function () use ($application, $status, $userId, $comment, &$changed): Application {
            $lockedApplication = Application::query()->lockForUpdate()->findOrFail($application->getKey());
            $oldStatus = $lockedApplication->status;

            if ($oldStatus === $status) {
                return $lockedApplication;
            }

            $lockedApplication->update(['status' => $status]);
            ApplicationStatusHistory::query()->create([
                'application_id' => $lockedApplication->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'changed_by' => $userId,
                'comment' => $comment,
            ]);
            $this->activities->record('application.status_changed', $lockedApplication, $userId, [
                'from' => $oldStatus->value,
                'to' => $status->value,
            ]);
            $changed = true;

            return $lockedApplication->fresh();
        });

        if ($changed && ! in_array($status, [ApplicationStatus::Draft, ApplicationStatus::Archived], true)) {
            SendApplicationStatusNotification::dispatch($application->id, $status->value)->afterCommit();
        }

        return $application;
    }
}
