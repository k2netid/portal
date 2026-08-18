<?php

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Security\Models\CspReport;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Support\DatabaseConsoleInts;

class CleanupCspReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:cleanup-csp-reports {--days=90 : Number of days to retain}';

    /**
     * The console command description.
     */
    protected $description = 'Remove Content Security Policy (CSP) reports older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysRaw = $this->option('days') ?? Setting::get('csp_reports_retention_days', 30);
        $days = DatabaseConsoleInts::fromMixed($daysRaw, 30);

        $count = CspReport::where('created_at', '<', now()->subDays($days))->delete();

        $this->info(sprintf('Deleted %d CSP report(s) older than %d days.', DatabaseConsoleInts::fromDeleteResult($count), $days));

        return 0;
    }
}
