<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Services\LicenseService;

class LicenseCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:check {--force : Force sync with JA-CP licensing hub even if not due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Core Engine license status and sync heartbeat with JA-CP licensing hub';

    public function handle(LicenseService $licenseService): int
    {
        $this->info('Checking Core Engine license status...');

        $status = $licenseService->getLicenseStatus();
        $tier = is_string($status['tier'] ?? null) ? $status['tier'] : 'community';
        $licenseStatus = is_string($status['status'] ?? null) ? $status['status'] : 'active';
        $maskedKey = is_string($status['masked_key'] ?? null) ? $status['masked_key'] : 'None (Community)';
        $domain = is_string($status['domain'] ?? null) ? $status['domain'] : 'localhost';

        $cpUrl = isset($status['control_plane_url']) && is_string($status['control_plane_url']) ? $status['control_plane_url'] : 'N/A';

        $this->table(
            ['Property', 'Value'],
            [
                ['Tier', strtoupper($tier)],
                ['Status', strtoupper($licenseStatus)],
                ['Active Key', $maskedKey],
                ['Bound Domain', $domain],
                ['Control Plane', $cpUrl],
            ]
        );

        $force = (bool) $this->option('force');
        $this->info('Sending heartbeat synchronization to JA-CP...');
        $syncResult = $licenseService->syncHeartbeat($force);

        if ($syncResult['success']) {
            $this->info('✓ '.$syncResult['message']);

            return Command::SUCCESS;
        }

        $this->warn('⚠ '.$syncResult['message']);

        return Command::SUCCESS;
    }
}
