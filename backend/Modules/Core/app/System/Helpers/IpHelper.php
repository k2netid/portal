<?php

namespace Modules\Core\System\Helpers;

use Illuminate\Http\Request;

class IpHelper
{
    /**
     * Get the client's real IP address, handling proxies.
     */
    public static function getClientIp(Request $request): string
    {
        return $request->ip() ?? '0.0.0.0';
    }

    /**
     * Check if an IP address is in a CIDR range.
     */
    public static function ipInSubnet(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }
}
