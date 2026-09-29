<?php

namespace Modules\Core\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustProxies
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int<0, 63>
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $trustedProxies = $this->getTrustedProxies($request);
        $request::setTrustedProxies($trustedProxies, (int) $this->headers);

        // Keep a stable resolved client IP for security/audit consumers.
        $resolvedIp = $request->ip();
        $remoteAddr = $request->server->get('REMOTE_ADDR');
        if (
            is_string($remoteAddr) &&
            filter_var($remoteAddr, FILTER_VALIDATE_IP) &&
            $this->isTrustedIngressProxy($remoteAddr, $trustedProxies)
        ) {
            $forwardedClientIp = $this->extractForwardedClientIp($request);
            if ($forwardedClientIp !== null) {
                $resolvedIp = $forwardedClientIp;
            }
        }

        $request->attributes->set('real_client_ip', $resolvedIp);

        return $next($request);
    }

    /**
     * Get trusted proxies list.
     *
     * @return array<int, string>
     */
    protected function getTrustedProxies(Request $request): array
    {
        $defaults = ['127.0.0.1', '::1'];
        $remoteAddr = $request->server->get('REMOTE_ADDR');
        if (is_string($remoteAddr) && filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
            $remoteAddrIsPublic = filter_var(
                $remoteAddr,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;

            if (! $remoteAddrIsPublic) {
                $defaults[] = $remoteAddr;
            }
        }

        $proxies = $defaults;
        $configured = config('app.trusted_proxies');
        if (is_string($configured) && trim($configured) !== '') {
            if (trim($configured) === '*') {
                return ['*'];
            }

            $proxies = array_values(array_filter(array_map(trim(...), explode(',', $configured)), static fn (string $ip): bool => $ip !== ''));
        } elseif (is_array($configured) && $configured !== []) {
            $normalized = array_values(array_filter(array_map(static fn ($ip): string => is_scalar($ip) ? trim((string) $ip) : '', $configured), static fn (string $ip): bool => $ip !== ''));
            if ($normalized !== []) {
                $proxies = $normalized;
            }
        }

        // Include Cloudflare CIDRs as trusted proxies so Symfony Request::getClientIp recognizes Cloudflare proxies
        return array_values(array_unique(array_merge($proxies, $this->getCloudflareCidrs())));
    }

    /**
     * @return array<int, string>
     */
    protected function getCloudflareCidrs(): array
    {
        return [
            // IPv4 ranges
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/13',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22',
            // IPv6 ranges
            '2400:cb00::/32',
            '2606:4700::/32',
            '2803:f800::/32',
            '2405:b500::/32',
            '2405:8100::/32',
            '2a06:98c0::/29',
            '2c0f:f248::/32',
        ];
    }

    protected function isCloudflareEdgeIp(string $ip): bool
    {
        foreach ($this->getCloudflareCidrs() as $cidr) {
            if ($this->ipInCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $trustedProxies
     */
    protected function isTrustedIngressProxy(string $remoteAddr, array $trustedProxies): bool
    {
        if ($this->isCloudflareEdgeIp($remoteAddr)) {
            return true;
        }

        $isPrivateOrReserved = filter_var(
            $remoteAddr,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
        if ($isPrivateOrReserved) {
            return true;
        }

        if (in_array('*', $trustedProxies, true)) {
            return true;
        }

        foreach ($trustedProxies as $proxy) {
            if ($proxy === $remoteAddr) {
                return true;
            }
            if (str_contains($proxy, '/') && $this->ipInCidr($remoteAddr, $proxy)) {
                return true;
            }
        }

        return false;
    }

    protected function extractForwardedClientIp(Request $request): ?string
    {
        // 1. Direct Cloudflare connecting IP (always the actual end-user IP when proxied by Cloudflare)
        $cfIp = $request->header('CF-Connecting-IP') ?? $request->header('True-Client-IP');
        if (is_string($cfIp) && filter_var($cfIp, FILTER_VALIDATE_IP)) {
            return $cfIp;
        }

        // 2. X-Real-IP if not a Cloudflare edge IP
        $realIp = $request->header('X-Real-IP');
        if (is_string($realIp) && filter_var($realIp, FILTER_VALIDATE_IP) && ! $this->isCloudflareEdgeIp($realIp)) {
            return $realIp;
        }

        // 3. X-Forwarded-For (extract end-user IP, ignoring Cloudflare edge IPs in the proxy chain)
        $forwardedFor = $request->header('X-Forwarded-For');
        if (is_string($forwardedFor)) {
            $ips = array_values(array_filter(array_map(trim(...), explode(',', $forwardedFor))));
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) && ! $this->isCloudflareEdgeIp($ip)) {
                    return $ip;
                }
            }
            if (! empty($ips) && filter_var($ips[0], FILTER_VALIDATE_IP)) {
                return $ips[0];
            }
        }

        return null;
    }

    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return false;
        }

        [$subnet, $bitsRaw] = explode('/', $cidr, 2);
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
