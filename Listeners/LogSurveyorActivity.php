<?php

declare(strict_types=1);

namespace Modules\Surveyor\Listeners;

use Modules\Surveyor\Models\Surveyor;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * HOOK — entity lifecycle generic (HasLifecycleHooks) untuk Surveyor.
 *
 * 1. created/updated/deleted -> activity log (satu listener, semua entity).
 * 2. STATUS-CHANGE pattern (task_status_changed): EntityUpdated mengecek
 *    changes['status'] — padanan estimate_accepted di legacy.
 */
class LogSurveyorActivity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof Surveyor) {
            return;
        }

        $this->activityLog->log(
            "Surveyor created: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof Surveyor) {
            return;
        }

        $this->activityLog->log(
            "Surveyor updated: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $event->changes],
        );

        $status = $event->changes['status'] ?? null;
        if ($status && $status['old'] !== $status['new']) {
            $this->activityLog->log(
                "Surveyor status changed: {$status['old']} -> {$status['new']}",
                $event->entity,
                $this->user(),
                ['event' => 'surveyor.status_changed', 'old' => $status['old'], 'new' => $status['new']],
            );
        }
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof Surveyor) {
            return;
        }

        $this->activityLog->log(
            "Surveyor deleted: " . $this->label($event->entity),
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    private function label($entity): string
    {
        return (string) ($entity->name ?? $entity->getKey());
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}
