<?php

declare(strict_types=1);

namespace Modules\Customer\Listeners;

use Modules\Customer\Models\Branch;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * HOOK — entity lifecycle generic (HasLifecycleHooks) untuk Branch.
 *
 * 1. created/updated/deleted -> activity log (satu listener, semua entity).
 * 2. STATUS-CHANGE pattern (task_status_changed): EntityUpdated mengecek
 *    changes['status'] — padanan estimate_accepted di legacy.
 */
class LogBranchActivity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof Branch) {
            return;
        }

        $this->activityLog->log(
            "Branch created: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof Branch) {
            return;
        }

        $this->activityLog->log(
            "Branch updated: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $event->changes],
        );

        $status = $event->changes['status'] ?? null;
        if ($status && $status['old'] !== $status['new']) {
            $this->activityLog->log(
                "Branch status changed: {$status['old']} -> {$status['new']}",
                $event->entity,
                $this->user(),
                ['event' => 'branch.status_changed', 'old' => $status['old'], 'new' => $status['new']],
            );
        }
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof Branch) {
            return;
        }

        $this->activityLog->log(
            "Branch deleted: " . $this->label($event->entity),
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
