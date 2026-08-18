<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;

/**
 * Security Maintenance Mode Service.
 * Provides per-module security pause with auto-expiry.
 * Used during code deployments, testing, and maintenance windows
 * to prevent false positives (e.g., file integrity alerts on code updates).
 * After maintenance ends, triggers post-maintenance actions:
 * - Regenerate file integrity baselines
 * - Log maintenance window for audit trail
 */
class SecurityMaintenanceService
{
    /** @var string Cache prefix for all maintenance keys */
    private const CACHE_PREFIX = 'security_maintenance:';

    /** @var int Maximum maintenance duration in minutes (4 hours) */
    private const MAX_DURATION_MINUTES = 240;

    /** @var int Default maintenance duration in minutes (1 hour) */
    private const DEFAULT_DURATION_MINUTES = 60;

    /** @var array<string> All available module keys */
    public const MODULES = [
        'waf',
        'scanner',
        'shield',
        'integrity',
        'autotune',
        'threatintel',
        'notifications',
    ];

    /**
     * Activate security maintenance mode for a specific duration.
     *
     * @param  array<string>  $modules  List of modules to pause or ["all"]
     * @param  int  $durationMinutes  Duration in minutes (max 240)
     * @return array{success: bool, message?: string, modules?: array<string>, duration?: int, expires_at?: string|null}
     */
    public function activate(array $modules = ['all'], int $durationMinutes = self::DEFAULT_DURATION_MINUTES): array
    {
        $durationMinutes = min($durationMinutes, self::MAX_DURATION_MINUTES);
        $durationMinutes = max(1, $durationMinutes);

        // Resolve 'all' to individual modules
        if (in_array('all', $modules, true)) {
            $modules = self::MODULES;
        }

        // Filter to valid modules only
        $modules = array_values(array_intersect($modules, self::MODULES));

        if ($modules === []) {
            return ['success' => false, 'message' => 'No valid modules specified.'];
        }

        $expiresAt = now()->addMinutes($durationMinutes);

        // Store maintenance state
        Cache::put(self::CACHE_PREFIX.'active', true, $expiresAt);
        Cache::put(self::CACHE_PREFIX.'modules', $modules, $expiresAt);
        Cache::put(self::CACHE_PREFIX.'started_at', now()->toISOString(), $expiresAt);
        Cache::put(self::CACHE_PREFIX.'expires_at', $expiresAt->toISOString(), $expiresAt);
        Cache::put(self::CACHE_PREFIX.'duration', $durationMinutes, $expiresAt);

        // Set per-module flags for O(1) lookup
        foreach ($modules as $module) {
            Cache::put(self::CACHE_PREFIX."module:{$module}", true, $expiresAt);
        }

        Log::channel('security')->info('Security maintenance mode ACTIVATED', [
            'modules' => $modules,
            'duration_minutes' => $durationMinutes,
            'expires_at' => $expiresAt->toISOString(),
        ]);

        return [
            'success' => true,
            'modules' => $modules,
            'duration' => $durationMinutes,
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    /**
     * Deactivate maintenance mode and run post-maintenance tasks.
     *
     * @return array{success: bool, message?: string, post_actions?: string}
     */
    public function deactivate(): array
    {
        if (! $this->isActive()) {
            return ['success' => false, 'message' => 'Maintenance mode is not active.'];
        }

        $status = $this->getStatus();

        // Clear all maintenance cache keys
        Cache::forget(self::CACHE_PREFIX.'active');
        Cache::forget(self::CACHE_PREFIX.'modules');
        Cache::forget(self::CACHE_PREFIX.'started_at');
        Cache::forget(self::CACHE_PREFIX.'expires_at');
        Cache::forget(self::CACHE_PREFIX.'duration');

        foreach (self::MODULES as $module) {
            Cache::forget(self::CACHE_PREFIX."module:{$module}");
        }

        Log::channel('security')->info('Security maintenance mode DEACTIVATED', [
            'was_active_for_modules' => $status['modules'],
        ]);

        // Run post-maintenance actions
        $this->runPostMaintenanceActions();

        return [
            'success' => true,
            'post_actions' => 'File integrity baselines regenerated.',
        ];
    }

    /**
     * Check if a specific security module is currently paused.
     */
    public function isModulePaused(string $module): bool
    {
        return (bool) Cache::get(self::CACHE_PREFIX."module:{$module}", false);
    }

    /**
     * Check if maintenance mode is active at all.
     */
    public function isActive(): bool
    {
        return (bool) Cache::get(self::CACHE_PREFIX.'active', false);
    }

    /**
     * Get current maintenance mode status.
     *
     * @return array{active: bool, modules: array<string>, started_at: string|null, expires_at: string|null, remaining_seconds: int}
     */
    public function getStatus(): array
    {
        if (! $this->isActive()) {
            return [
                'active' => false,
                'modules' => [],
                'started_at' => null,
                'expires_at' => null,
                'remaining_seconds' => 0,
            ];
        }

        $expiresAt = Cache::get(self::CACHE_PREFIX.'expires_at');
        $remaining = 0;

        if (is_string($expiresAt)) {
            $expiresCarbon = Carbon::parse($expiresAt);
            $remaining = max(0, (int) now()->diffInSeconds($expiresCarbon, false));
        }

        $rawModules = Cache::get(self::CACHE_PREFIX.'modules', []);
        $modules = is_array($rawModules) ? $rawModules : [];
        $modules = array_map(fn ($m): string => is_scalar($m) ? (string) $m : '', $modules);

        $rawStartedAt = Cache::get(self::CACHE_PREFIX.'started_at');
        $startedAt = is_string($rawStartedAt) ? $rawStartedAt : null;

        $rawExpiresAt = Cache::get(self::CACHE_PREFIX.'expires_at');
        $expiresAt = is_string($rawExpiresAt) ? $rawExpiresAt : null;

        return [
            'active' => true,
            'modules' => $modules,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'remaining_seconds' => $remaining,
        ];
    }

    /**
     * Run post-maintenance cleanup actions.
     * - Regenerate file integrity baselines
     * - Log the maintenance event
     */
    private function runPostMaintenanceActions(): void
    {
        // Regenerate file integrity baselines to prevent false positives
        try {
            $integrityService = app(FileIntegrityService::class);
            $stats = $integrityService->generateBaseline();

            Log::channel('security')->info('Post-maintenance: File integrity baselines regenerated', [
                'created' => $stats['created'],
                'updated' => $stats['updated'],
                'errors' => $stats['errors'],
            ]);
        } catch (\Exception $e) {
            Log::channel('security')->error('Post-maintenance: Failed to regenerate baselines', [
                'error' => $e->getMessage(),
            ]);
        }

        // Log maintenance event in security journal
        try {
            SecurityLog::log(
                'maintenance_ended',
                null,
                null,
                'Security maintenance mode ended. Baselines re-synced.',
                ['action' => 'post_maintenance_resync']
            );
        } catch (\Exception) {
            // Silently fail if DB is not available
        }
    }
}
