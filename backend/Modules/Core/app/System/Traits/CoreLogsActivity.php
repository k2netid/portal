<?php

declare(strict_types=1);

namespace Modules\Core\System\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\System\Models\ActivityLog;

/**
 * Trait to automatically log create, update, and delete events on Eloquent models.
 * Add this trait to any Core model that should be tracked in the activity journal.
 * Usage: `use \App\Traits\CoreLogsActivity;` in any Core Model class.
 */
trait CoreLogsActivity
{
    public static function bootCoreLogsActivity(): void
    {
        static::created(function (?Model $model): void {
            if (! $model instanceof Model) {
                return;
            }

            ActivityLog::log(
                'created',
                $model,
                ['attributes' => $model->getAttributes()],
            );
        });

        static::updated(function (?Model $model): void {
            if (! $model instanceof Model) {
                return;
            }

            $changes = $model->getChanges();
            unset($changes['updated_at'], $changes['remember_token']);

            if ($changes === []) {
                return;
            }

            $original = [];
            foreach (array_keys($changes) as $key) {
                $original[$key] = $model->getOriginal($key);
            }

            ActivityLog::log(
                'updated',
                $model,
                ['old' => $original, 'new' => $changes],
            );
        });

        static::deleted(function (?Model $model): void {
            if (! $model instanceof Model) {
                return;
            }

            ActivityLog::log(
                'deleted',
                $model,
                ['attributes' => $model->getAttributes()],
            );
        });
    }
}
