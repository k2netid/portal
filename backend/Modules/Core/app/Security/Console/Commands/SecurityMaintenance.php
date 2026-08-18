<?php

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SecurityMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run routine security maintenance tasks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting security maintenance...');

        $this->info("\n--- Cleaning up security logs ---");
        Artisan::call('security:cleanup-logs');
        $this->line(Artisan::output());

        $this->info("\n--- Auditing dependencies ---");
        Artisan::call('security:audit-dependencies');
        $this->line(Artisan::output());

        $this->info("\n--- Updating Cloudflare IPs ---");
        Artisan::call('security:update-cf-ips');
        $this->line(Artisan::output());

        $this->info("\nSecurity maintenance completed successfully.");

        return 0;
    }
}
