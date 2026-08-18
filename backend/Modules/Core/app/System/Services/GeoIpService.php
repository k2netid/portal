<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Setting;

class GeoIpService
{
    /**
     * Cache TTL in seconds (24 hours)
     */
    protected int $cacheTtl = 86400;

    /**
     * Get location data for an IP address
     *
     * @return array{country: string|null, city: string|null, country_code: string|null}
     */
    public function getLocation(string $ipAddress): array
    {
        // Skip for local/private IPs
        if ($this->isLocalIp($ipAddress)) {
            return $this->getDefaultLocation();
        }

        // Check cache first
        $cacheKey = "geoip:{$ipAddress}";
        /** @var array{country: string|null, city: string|null, country_code: string|null}|null $cached */
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        // Fetch from API
        $location = $this->fetchFromApi($ipAddress);

        // Cache the result
        Cache::put($cacheKey, $location, $this->cacheTtl);

        return $location;
    }

    /**
     * Fetch location data from ip-api.com
     *
     * @return array{country: string|null, city: string|null, country_code: string|null}
     */
    protected function fetchFromApi(string $ipAddress): array
    {
        try {
            // Using ip-api.com (free tier: 45 requests per minute)
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}", [
                'fields' => 'status,country,countryCode,city',
            ]);

            if ($response->successful()) {
                /** @var array<string, mixed> $data */
                $data = $response->json();

                if (($data['status'] ?? '') === 'success') {
                    return [
                        'country' => is_scalar($data['country'] ?? null) ? (string) $data['country'] : null,
                        'country_code' => is_scalar($data['countryCode'] ?? null) ? (string) $data['countryCode'] : null,
                        'city' => is_scalar($data['city'] ?? null) ? (string) $data['city'] : null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::channel('security')->warning('GeoIP lookup failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->getDefaultLocation();
    }

    /**
     * Check if IP is local/private
     */
    protected function isLocalIp(string $ipAddress): bool
    {
        // Localhost
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Private IP ranges
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ];

        $ip = ip2long($ipAddress);
        if ($ip === false) {
            return true; // Invalid IP, treat as local
        }

        foreach ($privateRanges as $range) {
            [$subnet, $mask] = explode('/', $range);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - (int) $mask)) - 1);

            if (($ip & $maskLong) === ($subnetLong & $maskLong)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get default location (when lookup fails or IP is local)
     *
     * @return array{country: string|null, city: string|null, country_code: string|null}
     */
    protected function getDefaultLocation(): array
    {
        return [
            'country' => null,
            'country_code' => null,
            'city' => null,
        ];
    }

    /**
     * Check if the IP address belongs to an allowed country
     */
    public function isCountryAllowed(string $ipAddress): bool
    {
        $allowedCountries = Setting::get('shield_allowed_countries', []);

        // If list is empty or not an array, all countries are allowed
        if (empty($allowedCountries) || ! is_array($allowedCountries)) {
            return true;
        }

        $location = $this->getLocation($ipAddress);
        $countryCode = $location['country_code'];

        if ($countryCode === null) {
            // If we can't determine the country, we allow it by default
            // unless we want to be very strict
            return true;
        }

        /** @var array<string> $allowedCountriesCodes */
        $allowedCountriesCodes = array_filter(array_map(fn ($c) => is_scalar($c) ? strtoupper((string) $c) : null, $allowedCountries));

        return in_array(strtoupper($countryCode), $allowedCountriesCodes);
    }

    /**
     * Clear GeoIP cache for a specific IP
     */
    public function clearCache(string $ipAddress): void
    {
        Cache::forget("geoip:{$ipAddress}");
    }
}
