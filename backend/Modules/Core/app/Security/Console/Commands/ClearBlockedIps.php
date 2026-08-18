<?php

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Security\Models\IpList;
use Modules\Core\Security\Services\SecurityService;

class ClearBlockedIps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:clear-blocked-ips {--ip= : Specific IP to unblock}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear permanently blocked IPs';

    /**
     * Execute the console command.
     */
    public function handle(SecurityService $securityService): int
    {
        $ip = $this->option('ip');

        if ($ip) {
            $this->info("Unblocking IP: {$ip}");
            $securityService->unblockIp((string) $ip);
            $this->info("IP {$ip} has been unblocked successfully.");

            return 0;
        }

        $this->info('Clearing all permanently blocked IPs...');

        $blockedIps = IpList::where('type', 'blocklist')->get();
        $count = $blockedIps->count();

        foreach ($blockedIps as $blockedIp) {
            /** @var IpList $blockedIp */
            $securityService->unblockIp($blockedIp->ip_address);
        }

        $this->info("Cleared {$count} blocked IPs.");

        return 0;
    }
}
