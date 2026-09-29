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
        $realIp = $request->attributes->get('real_client_ip');
        if (is_string($realIp) && $realIp !== '' && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        return $request->ip() ?? '0.0.0.0';
    }

    /**
     * Check if an IP address is in a CIDR range (supports IPv4 and IPv6).
     */
    public static function ipInSubnet(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return $ip === $range;
        }

        [$subnet, $bitsRaw] = explode('/', $range, 2);
        if (! is_numeric($bitsRaw)) {
            return false;
        }

        $ipBinary = @inet_pton($ip);
        $subnetBinary = @inet_pton($subnet);
        if ($ipBinary === false || $subnetBinary === false) {
            return false;
        }
        if (strlen($ipBinary) !== strlen($subnetBinary)) {
            return false;
        }

        $maxBits = strlen($ipBinary) * 8;
        $bits = (int) $bitsRaw;
        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($bits, 8);
        $remainingBits = $bits % 8;

        if ($fullBytes > 0 && substr($ipBinary, 0, $fullBytes) !== substr($subnetBinary, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (~(0xFF >> $remainingBits)) & 0xFF;

        return (ord($ipBinary[$fullBytes]) & $mask) === (ord($subnetBinary[$fullBytes]) & $mask);
    }
}

