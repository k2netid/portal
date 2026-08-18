<?php

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SystemClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all system caches (wrapper for optimize:clear)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Clearing system caches...');

        Artisan::call('optimize:clear');
        $this->info(Artisan::output());

        $this->info('System caches cleared successfully.');

        return 0;
    }
}
