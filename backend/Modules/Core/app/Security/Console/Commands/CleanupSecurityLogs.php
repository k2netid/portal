<?php

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Security\Models\SecurityLog;

class CleanupSecurityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:cleanup-logs {--days=30 : Number of days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old security logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up security logs older than {$days} days...");

        $cutoff = now()->subDays($days);

        $count = SecurityLog::where('created_at', '<', $cutoff)->delete();

        $this->info('Successfully deleted '.(is_numeric($count) ? (int) $count : 0).' old security logs.');

        return 0;
    }
}
