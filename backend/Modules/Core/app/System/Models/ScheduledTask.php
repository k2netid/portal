<?php

namespace Modules\Core\System\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Core\System\Traits\CoreLogsActivity;

/**
 * @property string $id
 * @property string $name
 * @property string $command
 * @property string $schedule
 * @property string|null $description
 * @property bool $is_active
 * @property array<string, mixed>|null $options
 * @property Carbon|null $last_run_at
 * @property string|null $status
 * @property string|null $output
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ScheduledTask extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_scheduled_tasks';

    use CoreLogsActivity;

    protected $fillable = [
        'name',
        'command',
        'schedule',
        'description',
        'is_active',
        'options',
        'last_run_at',
        'status',
        'output',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    /**
     * Whitelist of allowed commands that can be executed from UI
     * Only these commands are safe to run via admin interface
     */
    const ALLOWED_COMMANDS = [
        // Cache management
        'cache:clear',
        'cache:warm',
        'system:clear-cache',

        // Content & Media
        'content:publish-scheduled',
        'media:generate-thumbnails',
        'media:cleanup-temp',
        'media:sync-from-disk',
        'theme:scan-register',

        // Health & diagnostics
        'system:health-check',
        'system:audit',
        'config:clear',
        'route:clear',
        'view:clear',

        // Intelligence & Search
        'search:reindex',
        'search:index-health',
        'analytics:cleanup',

        // Backup
        'system:backup',

        // Security
        'security:clear-blocked-ips',
        'security:clear-rate-limit',
        'security:audit-dependencies',
        'security:cleanup-logs',
        'security:update-cf-ips',
        'security:maintenance',

        // Maintenance & Cleanup
        'logs:cleanup',
        'logs:cleanup-slow-queries',
        'logs:cleanup-csp-reports',

        // Queue Management
        'queue:work',
        'queue:restart',
        'queue:flush',
        'queue:prune-failed',
        'queue:retry',
        'queue:monitor',

        // System Optimization
        'optimize',
        'optimize:clear',
        'sanctum:prune-expired',
    ];

    /**
     * Documentation of commands that are strictly for backend CLI execution.
     * These commands are destructive, require args, or mutate dev files.
     * They must NEVER be added to ALLOWED_COMMANDS.
     */
    const BACKEND_ONLY_COMMANDS = [
        // Theme Development
        'theme:build',
        'theme:checksum',
        'theme:make',
        'theme:package',
        'theme:paths',
        'theme:validate',
        'theme:staging-uploaded',
        'theme:backfill-source',
        'layout:themes:backfill-janari-parent',

        // System Development
        'dynamic:openapi',

        // Core Database
        'migrate',
        'migrate:rollback',
        'migrate:fresh',
        'db:seed',
    ];

    /**
     * Commands that are NEVER allowed from UI
     */
    const BLOCKED_COMMANDS = [
        'migrate',
        'migrate:fresh',
        'migrate:rollback',
        'migrate:reset',
        'db:wipe',
        'db:seed',
        'down',
        'key:generate',
        'env:decrypt',
        'tinker',
        'make:',
    ];

    /**
     * Check if a command is allowed to be executed
     */
    public static function isCommandAllowed(string $command): bool
    {
        // Extract base command (before any arguments/options)
        $parts = explode(' ', $command, 2);
        $baseCommand = trim($parts[0] ?? '');

        // Check against blocked commands (including partials like 'make:')
        foreach (self::BLOCKED_COMMANDS as $blocked) {
            if (str_starts_with($baseCommand, $blocked)) {
                return false;
            }
        }

        // Check if in allowed list
        return in_array($baseCommand, self::ALLOWED_COMMANDS);
    }

    /**
     * Validate cron expression
     */
    public static function isValidCronExpression(string $expression): bool
    {
        try {
            return CronExpression::isValidExpression($expression);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get allowed commands list for UI dropdown
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function getAllowedCommands(): array
    {
        $commands = array_map(fn (string $cmd) => [
            'value' => $cmd,
            'label' => ucfirst(str_replace([':', '-'], [' › ', ' '], $cmd)),
        ], self::ALLOWED_COMMANDS);

        // Sort alphabetically by label
        usort($commands, fn (array $a, array $b) => strcasecmp((string) $a['label'], (string) $b['label']));

        return $commands;
    }

    /**
     * Get next run time based on cron expression
     */
    public function getNextRunAt(): ?\DateTime
    {
        if (! $this->schedule || ! self::isValidCronExpression($this->schedule)) {
            return null;
        }

        try {
            $cron = new CronExpression($this->schedule);

            return $cron->getNextRunDate();
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param  Builder<ScheduledTask>  $query
     * @return Builder<ScheduledTask>
     */
    public function scopeActive($query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<ScheduledTask>  $query
     * @return Builder<ScheduledTask>
     */
    public function scopeDue($query): Builder
    {
        return $query->active()
            ->where(function ($q): void {
                $q->whereNull('last_run_at')
                    ->orWhere('last_run_at', '<', now()->subMinute());
            });
    }
}
