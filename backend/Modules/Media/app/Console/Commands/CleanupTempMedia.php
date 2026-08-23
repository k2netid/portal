<?php

namespace Modules\Media\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class CleanupTempMedia extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'media:cleanup-temp {--hours=24 : The number of hours to keep temp files}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up temporary media files older than specified hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = $this->option('hours');
        $hours = is_numeric($hours) ? (int) $hours : 24;

        $this->info("Cleaning up temporary files older than {$hours} hours...");

        $tempPath = storage_path('app/temp');

        if (! File::isDirectory($tempPath)) {
            $this->info('Temp directory does not exist. Nothing to cleanup.');

            return 0;
        }

        $files = File::files($tempPath);
        $count = 0;
        $now = now();

        foreach ($files as $file) {
            $lastModified = $file->getMTime();
            $lastModifiedDate = Carbon::createFromTimestamp($lastModified);

            if ($lastModifiedDate->diffInHours($now) >= $hours) {
                File::delete($file->getRealPath());
                $count++;
            }
        }

        $this->info("Deleted {$count} temporary files.");

        return 0;
    }
}
