<?php

namespace Modules\Core\Security\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class UpdateCloudflareIps extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:update-cf-ips';

    /**
     * The console command description.
     */
    protected $description = 'Update local cache of Cloudflare IP ranges';

    /**
     * The path to store the cached IPs.
     */
    protected string $cachePath;

    public function __construct()
    {
        parent::__construct();
        $this->cachePath = storage_path('framework/cache/cloudflare_ips.php');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching Cloudflare IP ranges...');

        try {
            $ipv4 = Http::get('https://www.cloudflare.com/ips-v4')->throw()->body();
            $ipv6 = Http::get('https://www.cloudflare.com/ips-v6')->throw()->body();

            $ips = array_merge(
                explode("\n", trim($ipv4)),
                explode("\n", trim($ipv6))
            );

            // Filter valid IPs/CIDRs only
            $ips = array_filter($ips, function ($ip) {
                return ! empty($ip) && (
                    filter_var($ip, FILTER_VALIDATE_IP) ||
                    $this->isValidCidr($ip)
                );
            });

            if (empty($ips)) {
                $this->error('No IPs received from Cloudflare.');

                return 1;
            }

            $content = "<?php\n\nreturn ".var_export(array_values($ips), true).";\n";

            // Ensure directory exists
            if (! File::isDirectory(dirname($this->cachePath))) {
                File::makeDirectory(dirname($this->cachePath), 0755, true);
            }

            File::put($this->cachePath, $content);

            $this->info('Successfully updated Cloudflare IPs: '.count($ips).' ranges.');

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to update Cloudflare IPs: '.$e->getMessage());

            return 1;
        }
    }

    protected function isValidCidr(string $cidr): bool
    {
        $parts = explode('/', $cidr);
        if (count($parts) != 2) {
            return false;
        }

        $ip = $parts[0];
        $netmask = $parts[1];

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        if (! is_numeric($netmask)) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $netmask >= 0 && $netmask <= 32;
        } else {
            return $netmask >= 0 && $netmask <= 128;
        }
    }
}
