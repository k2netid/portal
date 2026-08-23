<?php

namespace Modules\Analytics\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Support\DatabaseConsoleInts;
use Modules\Analytics\Models\SlowQuery;

class CleanupSlowQueryLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:cleanup-slow-queries {--days=30 : Number of days to retain}';

    /**
     * The console command description.
     */
    protected $description = 'Remove slow query logs older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysRaw = $this->option('days') ?? Setting::get('slow_query_retention_days', 7);
        $days = DatabaseConsoleInts::fromMixed($daysRaw, 7);

        $count = SlowQuery::where('created_at', '<', now()->subDays($days))->delete();

        $this->info(sprintf('Deleted %d slow query log(s) older than %d days.', DatabaseConsoleInts::fromDeleteResult($count), $days));

        return 0;
    }
}
