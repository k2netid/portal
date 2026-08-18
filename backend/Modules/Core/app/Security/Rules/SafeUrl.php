<?php

declare(strict_types=1);

namespace Modules\Core\Security\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\PotentiallyTranslatedString;
use Modules\Core\System\Helpers\IpHelper;

class SafeUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (! $host) {
            $fail('The :attribute must have a valid host.');

            return;
        }

        // Check if host is an IP address
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if ($this->isInternalIp($host)) {
                $this->logPotentialSsrf($value, $host);
                $fail('The :attribute cannot point to an internal or private network.');
            }

            return;
        }

        // Host is a domain name, resolve it to check IP
        $ips = @gethostbynamel($host);
        if ($ips === false) {
            // DNS resolution failed or host doesn't exist
            return;
        }

        foreach ($ips as $ip) {
            if ($this->isInternalIp($ip)) {
                $this->logPotentialSsrf($value, $ip);
                $fail('The :attribute resolves to an internal or private network.');

                return;
            }
        }
    }

    /**
     * Check if an IP address is internal/private.
     */
    protected function isInternalIp(string $ip): bool
    {
        // Reserved/Private IP ranges (RFC 1918, etc.)
        $privateRanges = [
            '127.0.0.0/8',      // Loopback
            '10.0.0.0/8',       // Private Class A
            '172.16.0.0/12',    // Private Class B
            '192.168.0.0/16',   // Private Class C
            '169.254.0.0/16',   // Link-local (Cloud metadata services)
            '0.0.0.0/8',        // Current network
            '100.64.0.0/10',    // Carrier Grade NAT
            '224.0.0.0/4',      // Multicast
            '240.0.0.0/4',      // Reserved
            '::1/128',          // IPv6 Loopback
            'fc00::/7',         // IPv6 Private/Unique Local
            'fe80::/10',        // IPv6 Link-local
        ];

        foreach ($privateRanges as $range) {
            if ($this->ipInNetwork($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is within a CIDR range.
     */
    protected function ipInNetwork(string $ip, string $range): bool
    {
        if (str_contains($ip, ':')) {
            // IPv6 check (simplified version for fc00:: and fe80::)
            return str_starts_with(strtolower($ip), strtolower(str_replace('/7', '', str_replace('/10', '', $range))));
        }

        if (! str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }
        $mask = -1 << (32 - (int) $bits);
        $subnetLong &= $mask;

        return ($ipLong & $mask) === $subnetLong;
    }

    /**
     * Log a potential SSRF attempt.
     */
    protected function logPotentialSsrf(string $url, string $ip): void
    {
        Log::warning('Potential SSRF attempt detected in Webhook URL', [
            'url' => $url,
            'resolved_ip' => $ip,
            'user_id' => Auth::id(),
            'ip_address' => IpHelper::getClientIp(request()),
        ]);
    }
}
