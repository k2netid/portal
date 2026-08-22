<?php

namespace Modules\Core\System\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        // System & Diagnostics
        'system:backup',
        'system:health-check',
        'system:audit',
        'system:clear-cache',
        'license:check',
        'mail:process-scheduled',

        // Security & WAF
        'security:update-cf-ips',
        'security:cleanup-logs',
        'security:maintenance',
        'security:audit-dependencies',
        'security:clear-blocked-ips',
        'security:clear-rate-limit',
        'logs:cleanup-csp-reports',
        'sanctum:prune-expired',

        // Maintenance & Logs
        'logs:cleanup',

        // Queue Management
        'queue:prune-failed',
        'queue:restart',
        'queue:flush',
        'queue:retry',
        'queue:monitor',

        // System Optimization & Cache
        'optimize',
        'optimize:clear',
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
    ];

    /**
     * Documentation of commands that are strictly for backend CLI execution.
     * These commands are destructive, require args, or mutate dev files.
     * They must NEVER be added to ALLOWED_COMMANDS.
     */
    const BACKEND_ONLY_COMMANDS = [
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
     * Complete Command Catalog with metadata and prerequisites
     *
     * @return array<string, array{
     *     command: string,
     *     name: string,
     *     category: string,
     *     description: string,
     *     is_recommended: bool,
     *     default_schedule: string,
     *     prerequisites: array<string>
     * }>
     */
    public static function getCommandCatalog(): array
    {
        return [
            'system:backup' => [
                'command' => 'system:backup',
                'name' => 'Run Daily Backup',
                'category' => 'Backup',
                'description' => 'Generates daily automated database backup snapshot for disaster recovery.',
                'is_recommended' => true,
                'default_schedule' => '0 1 * * *',
                'prerequisites' => ['database', 'php_zip', 'storage_write'],
            ],
            'queue:prune-failed' => [
                'command' => 'queue:prune-failed',
                'name' => 'Prune Failed Jobs',
                'category' => 'Queue',
                'description' => 'Removes failed background jobs older than 48 hours from the queue database table.',
                'is_recommended' => true,
                'default_schedule' => '0 2 * * *',
                'prerequisites' => ['database', 'table_failed_jobs'],
            ],
            'logs:cleanup' => [
                'command' => 'logs:cleanup',
                'name' => 'Cleanup System Logs',
                'category' => 'Maintenance',
                'description' => 'Prunes activity logs, login histories, and general system logs older than retention period (default 90 days).',
                'is_recommended' => true,
                'default_schedule' => '0 0 * * 1',
                'prerequisites' => ['database'],
            ],
            'logs:cleanup-csp-reports' => [
                'command' => 'logs:cleanup-csp-reports',
                'name' => 'Cleanup CSP Reports',
                'category' => 'Security',
                'description' => 'Removes browser Content Security Policy violation reports older than 90 days.',
                'is_recommended' => true,
                'default_schedule' => '0 0 * * 3',
                'prerequisites' => ['database'],
            ],
            'security:cleanup-logs' => [
                'command' => 'security:cleanup-logs',
                'name' => 'Cleanup Security Event Logs',
                'category' => 'Security',
                'description' => 'Purges security WAF and bot audit logs older than retention period.',
                'is_recommended' => true,
                'default_schedule' => '0 1 * * 0',
                'prerequisites' => ['database'],
            ],
            'security:update-cf-ips' => [
                'command' => 'security:update-cf-ips',
                'name' => 'Update Cloudflare IPs',
                'category' => 'Security',
                'description' => 'Synchronizes Cloudflare reverse proxy IPv4/IPv6 ranges to maintain accurate client IP detection and WAF rules.',
                'is_recommended' => true,
                'default_schedule' => '0 4 * * *',
                'prerequisites' => ['outbound_http', 'storage_write'],
            ],
            'sanctum:prune-expired' => [
                'command' => 'sanctum:prune-expired',
                'name' => 'Prune Revoked Tokens',
                'category' => 'Security',
                'description' => 'Deletes expired and revoked authentication tokens from the database.',
                'is_recommended' => true,
                'default_schedule' => '0 5 * * *',
                'prerequisites' => ['database', 'table_tokens'],
            ],
            'security:maintenance' => [
                'command' => 'security:maintenance',
                'name' => 'Run Security Maintenance',
                'category' => 'Security',
                'description' => 'Executes routine security cleanup, IP table sync, and token validation.',
                'is_recommended' => false,
                'default_schedule' => '0 3 * * 0',
                'prerequisites' => ['database'],
            ],
            'security:audit-dependencies' => [
                'command' => 'security:audit-dependencies',
                'name' => 'Security Dependency Vulnerability Scan',
                'category' => 'Security',
                'description' => 'Scans composer and npm package dependencies for known CVE advisories.',
                'is_recommended' => false,
                'default_schedule' => '0 2 * * 0',
                'prerequisites' => ['outbound_http', 'database'],
            ],
            'security:clear-blocked-ips' => [
                'command' => 'security:clear-blocked-ips',
                'name' => 'Clear Blocked IPs',
                'category' => 'Security',
                'description' => 'Unblocks temporarily rate-limited and banned client IP addresses.',
                'is_recommended' => false,
                'default_schedule' => '0 0 * * 0',
                'prerequisites' => ['database'],
            ],
            'security:clear-rate-limit' => [
                'command' => 'security:clear-rate-limit',
                'name' => 'Clear Rate Limit Lockouts',
                'category' => 'Security',
                'description' => 'Resets authentication login attempt counters and lockout blocks.',
                'is_recommended' => false,
                'default_schedule' => '0 0 * * *',
                'prerequisites' => ['database'],
            ],
            'system:health-check' => [
                'command' => 'system:health-check',
                'name' => 'System Health Diagnostic',
                'category' => 'Diagnostics',
                'description' => 'Runs hardware CPU, memory, disk, database, and cache service health checks.',
                'is_recommended' => false,
                'default_schedule' => '*/30 * * * *',
                'prerequisites' => ['database'],
            ],
            'system:audit' => [
                'command' => 'system:audit',
                'name' => 'Core File Integrity Audit',
                'category' => 'Security',
                'description' => 'Audits SHA-256 integrity of Tier-A core system files against baseline signatures.',
                'is_recommended' => false,
                'default_schedule' => '0 0 * * 0',
                'prerequisites' => ['storage_write'],
            ],
            'license:check' => [
                'command' => 'license:check',
                'name' => 'License Verification & Sync',
                'category' => 'System',
                'description' => 'Verifies engine license validity and synchronization with upstream licensing authority.',
                'is_recommended' => false,
                'default_schedule' => '0 0 * * *',
                'prerequisites' => ['outbound_http', 'database'],
            ],
            'optimize' => [
                'command' => 'optimize',
                'name' => 'System Optimization',
                'category' => 'System',
                'description' => 'Precompiles framework route, configuration, and view caches for high-throughput production execution.',
                'is_recommended' => false,
                'default_schedule' => '0 6 * * *',
                'prerequisites' => ['bootstrap_cache_write', 'storage_write'],
            ],
            'optimize:clear' => [
                'command' => 'optimize:clear',
                'name' => 'Clear Compiled Optimization Files',
                'category' => 'System',
                'description' => 'Removes all precompiled route, configuration, and view cache files.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * 0',
                'prerequisites' => ['bootstrap_cache_write'],
            ],
            'system:clear-cache' => [
                'command' => 'system:clear-cache',
                'name' => 'Flush All System & Framework Caches',
                'category' => 'System',
                'description' => 'Clears config, route, view, and data cache layers simultaneously.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * 0',
                'prerequisites' => ['bootstrap_cache_write', 'storage_write'],
            ],
            'cache:clear' => [
                'command' => 'cache:clear',
                'name' => 'Flush Application Cache',
                'category' => 'Cache',
                'description' => 'Flushes all application key-value cache stores.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * 0',
                'prerequisites' => ['storage_write'],
            ],
            'config:clear' => [
                'command' => 'config:clear',
                'name' => 'Clear Config Cache',
                'category' => 'Cache',
                'description' => 'Clears cached configuration files.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * 0',
                'prerequisites' => ['bootstrap_cache_write'],
            ],
            'route:clear' => [
                'command' => 'route:clear',
                'name' => 'Clear Route Cache',
                'category' => 'Cache',
                'description' => 'Clears compiled route cache files.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * 0',
                'prerequisites' => ['bootstrap_cache_write'],
            ],
            'view:clear' => [
                'command' => 'view:clear',
                'name' => 'Clear View Cache',
                'category' => 'Cache',
                'description' => 'Clears compiled Blade view files.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * 0',
                'prerequisites' => ['storage_write'],
            ],
            'queue:restart' => [
                'command' => 'queue:restart',
                'name' => 'Restart Queue Workers',
                'category' => 'Queue',
                'description' => 'Signals all background queue worker daemons to gracefully restart after current job.',
                'is_recommended' => false,
                'default_schedule' => '0 4 * * *',
                'prerequisites' => ['database'],
            ],
            'queue:flush' => [
                'command' => 'queue:flush',
                'name' => 'Flush Failed Queue Jobs',
                'category' => 'Queue',
                'description' => 'Deletes all recorded failed jobs immediately.',
                'is_recommended' => false,
                'default_schedule' => '0 0 1 * *',
                'prerequisites' => ['database', 'table_failed_jobs'],
            ],
            'queue:retry' => [
                'command' => 'queue:retry',
                'name' => 'Retry All Failed Queue Jobs',
                'category' => 'Queue',
                'description' => 'Pushes all failed queue jobs back into active processing queues.',
                'is_recommended' => false,
                'default_schedule' => '0 3 * * *',
                'prerequisites' => ['database', 'table_failed_jobs'],
            ],
            'queue:monitor' => [
                'command' => 'queue:monitor',
                'name' => 'Monitor Queue Backlog',
                'category' => 'Queue',
                'description' => 'Monitors queue backlog size and alerts if threshold exceeded.',
                'is_recommended' => false,
                'default_schedule' => '*/15 * * * *',
                'prerequisites' => ['database'],
            ],
        ];
    }

    /**
     * Evaluate real-time system status for all prerequisites
     *
     * @return array<string, array{id: string, name: string, description: string, met: bool, message: string}>
     */
    public static function checkAllPrerequisites(): array
    {
        $dbMet = false;
        $dbMsg = 'Database connection OK';
        try {
            DB::connection()->getPdo();
            $dbMet = true;
        } catch (\Throwable $e) {
            $dbMsg = 'Database connection failed: '.$e->getMessage();
        }

        $storageMet = is_writable(storage_path());
        $bootstrapMet = is_writable(app()->bootstrapPath('cache'));
        $gdMet = extension_loaded('gd') || extension_loaded('imagick');
        $zipMet = extension_loaded('zip');
        $httpMet = extension_loaded('curl') || (bool) ini_get('allow_url_fopen');

        $failedJobsTable = false;
        $tokensTable = false;
        try {
            $failedJobsTable = Schema::hasTable('sys_failed_jobs') || Schema::hasTable('failed_jobs');
            $tokensTable = Schema::hasTable('personal_access_tokens') || Schema::hasTable('srv_personal_access_tokens') || Schema::hasTable('sys_personal_access_tokens');
        } catch (\Throwable) {
        }

        return [
            'database' => [
                'id' => 'database',
                'name' => 'Database Connection',
                'description' => 'Active PDO connection to application database.',
                'met' => $dbMet,
                'message' => $dbMsg,
            ],
            'storage_write' => [
                'id' => 'storage_write',
                'name' => 'Storage Directory Writable',
                'description' => 'Write permissions on storage/ directory.',
                'met' => $storageMet,
                'message' => $storageMet ? 'storage/ directory is writable' : 'storage/ directory is not writable',
            ],
            'bootstrap_cache_write' => [
                'id' => 'bootstrap_cache_write',
                'name' => 'Bootstrap Cache Writable',
                'description' => 'Write permissions on bootstrap/cache/ directory.',
                'met' => $bootstrapMet,
                'message' => $bootstrapMet ? 'bootstrap/cache/ directory is writable' : 'bootstrap/cache/ directory is not writable',
            ],
            'php_gd' => [
                'id' => 'php_gd',
                'name' => 'PHP GD / Imagick Extension',
                'description' => 'Image processing extension (GD or Imagick) enabled.',
                'met' => $gdMet,
                'message' => $gdMet ? 'PHP image manipulation extension is available' : 'Missing PHP GD or Imagick extension',
            ],
            'php_zip' => [
                'id' => 'php_zip',
                'name' => 'PHP Zip Extension',
                'description' => 'ZipArchive extension enabled for archive operations.',
                'met' => $zipMet,
                'message' => $zipMet ? 'PHP Zip extension is enabled' : 'Missing PHP Zip extension (required for backups)',
            ],
            'outbound_http' => [
                'id' => 'outbound_http',
                'name' => 'Outbound HTTP (cURL)',
                'description' => 'Outbound internet connection via cURL or streams enabled.',
                'met' => $httpMet,
                'message' => $httpMet ? 'Outbound HTTP / cURL is enabled' : 'cURL or allow_url_fopen is disabled',
            ],
            'table_failed_jobs' => [
                'id' => 'table_failed_jobs',
                'name' => 'Failed Jobs Table',
                'description' => 'Queue sys_failed_jobs database table exists.',
                'met' => $failedJobsTable,
                'message' => $failedJobsTable ? 'sys_failed_jobs table exists' : 'sys_failed_jobs table is missing (run migrations)',
            ],
            'table_tokens' => [
                'id' => 'table_tokens',
                'name' => 'Personal Access Tokens Table',
                'description' => 'Personal access tokens database table exists.',
                'met' => $tokensTable,
                'message' => $tokensTable ? 'Tokens table exists' : 'Tokens table is missing',
            ],
        ];
    }

    /**
     * Get allowed commands list for UI dropdown with rich metadata
     *
     * @return array<int, array{
     *     value: string,
     *     label: string,
     *     category: string,
     *     description: string,
     *     is_recommended: bool,
     *     default_schedule: string,
     *     prerequisites: array<string>,
     *     prerequisites_met: bool,
     *     unmet_prerequisites: array<string>
     * }>
     */
    public static function getAllowedCommands(): array
    {
        $catalog = self::getCommandCatalog();
        $prereqs = self::checkAllPrerequisites();

        $commands = [];
        foreach (self::ALLOWED_COMMANDS as $cmd) {
            $meta = $catalog[$cmd] ?? [
                'command' => $cmd,
                'name' => ucfirst(str_replace([':', '-'], [' › ', ' '], $cmd)),
                'category' => 'General',
                'description' => '',
                'is_recommended' => false,
                'default_schedule' => '0 0 * * *',
                'prerequisites' => ['database'],
            ];

            $unmet = [];
            foreach ($meta['prerequisites'] as $prereqKey) {
                if (isset($prereqs[$prereqKey]) && ! $prereqs[$prereqKey]['met']) {
                    $unmet[] = $prereqs[$prereqKey]['name'];
                }
            }

            $commands[] = [
                'value' => $cmd,
                'label' => $meta['name'],
                'category' => $meta['category'],
                'description' => $meta['description'],
                'is_recommended' => $meta['is_recommended'],
                'default_schedule' => $meta['default_schedule'],
                'prerequisites' => $meta['prerequisites'],
                'prerequisites_met' => empty($unmet),
                'unmet_prerequisites' => $unmet,
            ];
        }

        // Sort alphabetically by category then label
        usort($commands, function (array $a, array $b): int {
            $catCmp = strcasecmp((string) $a['category'], (string) $b['category']);
            if ($catCmp !== 0) {
                return $catCmp;
            }

            return strcasecmp((string) $a['label'], (string) $b['label']);
        });

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
