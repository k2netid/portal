<?php

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\LoginHistory;
use Modules\Core\System\Models\Setting;

class CleanupOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:cleanup 
                            {--days= : Override retention days from settings}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old activity, security, and login history logs based on retention settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDaysRaw = $this->option('days') ?? Setting::get('log_retention_days', 90);
        $retentionDays = is_numeric($retentionDaysRaw) ? (int) $retentionDaysRaw : 90;

        if ($retentionDays <= 0) {
            $this->info('Log retention is set to keep forever (0 days). No cleanup performed.');

            return 0;
        }

        $cutoffDate = now()->subDays($retentionDays);
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Cleaning up logs older than {$retentionDays} days (before {$cutoffDate->format('Y-m-d')})");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No records will be deleted');
        }

        $totalDeleted = 0;

        // Activity Logs
        $activityCount = ActivityLog::where('created_at', '<', $cutoffDate)->count();
        if (! $dryRun && $activityCount > 0) {
            ActivityLog::where('created_at', '<', $cutoffDate)->delete();
        }
        $this->line("  Activity Logs: {$activityCount} records".($dryRun ? ' (would be deleted)' : ' deleted'));
        $totalDeleted += $activityCount;

        // Security Logs
        $securityCount = SecurityLog::where('created_at', '<', $cutoffDate)->count();
        if (! $dryRun && $securityCount > 0) {
            SecurityLog::where('created_at', '<', $cutoffDate)->delete();
        }
        $this->line("  Security Logs: {$securityCount} records".($dryRun ? ' (would be deleted)' : ' deleted'));
        $totalDeleted += $securityCount;

        // Login History
        $loginCount = LoginHistory::where('created_at', '<', $cutoffDate)->count();
        if (! $dryRun && $loginCount > 0) {
            LoginHistory::where('created_at', '<', $cutoffDate)->delete();
        }
        $this->line("  Login History: {$loginCount} records".($dryRun ? ' (would be deleted)' : ' deleted'));
        $totalDeleted += $loginCount;

        $this->newLine();
        $this->info("Total: {$totalDeleted} records ".($dryRun ? 'would be' : '').' cleaned up');

        if (! $dryRun && $totalDeleted > 0) {
            Log::info("Log cleanup completed: {$totalDeleted} records deleted", [
                'retention_days' => $retentionDays,
                'cutoff_date' => $cutoffDate->toDateString(),
                'activity_logs' => $activityCount,
                'security_logs' => $securityCount,
                'login_history' => $loginCount,
            ]);
        }

        return 0;
    }
}
