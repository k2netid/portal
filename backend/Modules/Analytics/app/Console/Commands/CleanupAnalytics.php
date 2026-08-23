<?php

namespace Modules\Analytics\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Analytics\Models\AnalyticsEvent;
use Modules\Analytics\Models\AnalyticsSession;
use Modules\Analytics\Models\AnalyticsVisit;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Support\DatabaseConsoleInts;

class CleanupAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:cleanup 
        {--days=90 : Number of days to retain analytics data}
        {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old analytics data to manage database size';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDaysRaw = $this->option('days') ?? Setting::get('analytics_retention_days', 90);
        $retentionDays = DatabaseConsoleInts::fromMixed($retentionDaysRaw, 90);
        $dryRun = $this->option('dry-run');
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("Analytics Cleanup - Retention: {$retentionDays} days");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        $this->newLine();

        // Count records to delete
        $visitsCount = DatabaseConsoleInts::fromDeleteResult(AnalyticsVisit::where('visited_at', '<', $cutoffDate)->count());
        $eventsCount = DatabaseConsoleInts::fromDeleteResult(AnalyticsEvent::where('occurred_at', '<', $cutoffDate)->count());
        $sessionsCount = DatabaseConsoleInts::fromDeleteResult(AnalyticsSession::where('started_at', '<', $cutoffDate)->count());

        $this->table(
            ['Table', 'Records to Delete'],
            [
                ['analytics_visits', $visitsCount],
                ['analytics_events', $eventsCount],
                ['analytics_sessions', $sessionsCount],
            ]
        );

        $totalRecords = $visitsCount + $eventsCount + $sessionsCount;

        if ($totalRecords === 0) {
            $this->info('No records to delete.');

            return 0;
        }

        if ($dryRun) {
            $this->info("Would delete {$totalRecords} total records.");

            return 0;
        }

        $this->newLine();
        $this->info('Deleting records...');

        // Delete visits
        $countVisits = AnalyticsVisit::where('visited_at', '<', $cutoffDate)->delete();
        $this->info(sprintf('Deleted %d old visits.', DatabaseConsoleInts::fromDeleteResult($countVisits)));

        // Delete events
        $countEvents = AnalyticsEvent::where('occurred_at', '<', $cutoffDate)->delete();
        $this->info(sprintf('Deleted %d old events.', DatabaseConsoleInts::fromDeleteResult($countEvents)));

        // Delete sessions
        $countSessions = AnalyticsSession::where('started_at', '<', $cutoffDate)->delete();
        $this->info(sprintf('Deleted %d old sessions.', DatabaseConsoleInts::fromDeleteResult($countSessions)));

        $this->newLine();
        $totalDeleted = DatabaseConsoleInts::fromDeleteResult($countVisits) + DatabaseConsoleInts::fromDeleteResult($countEvents) + DatabaseConsoleInts::fromDeleteResult($countSessions);
        $this->info(sprintf('Cleanup complete! Deleted %d total records.', $totalDeleted));

        Log::info('Analytics cleanup completed', [
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'deleted' => [
                'visits' => $countVisits,
                'events' => $countEvents,
                'sessions' => $countSessions,
            ],
        ]);

        return 0;
    }
}
