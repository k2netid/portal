<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Setting;

/**
 * Threat Intelligence Service.
 * Integrates with AbuseIPDB to check IP reputation
 * and report malicious IPs.
 */
class ThreatIntelService
{
    /** @var int Cache duration for IP reputation results (24 hours) */
    private const CACHE_TTL_SECONDS = 86400;

    /** @var int Confidence threshold for auto-blocking (0-100) */
    private const AUTO_BLOCK_THRESHOLD = 75;

    /**
     * Check an IP's reputation against AbuseIPDB.
     *
     * @return array{score: int, is_malicious: bool, country: string|null, usage_type: string|null, isp: string|null, total_reports: int}|null
     */
    public function checkIp(string $ipAddress): ?array
    {
        $cacheKey = "threat_intel:abuseipdb:{$ipAddress}";

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            /** @var array{score: int, is_malicious: bool, country: string|null, usage_type: string|null, isp: string|null, total_reports: int} $cached */
            return $cached;
        }

        $apiKey = $this->getApiKey();
        if ($apiKey === null) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get('https://api.abuseipdb.com/api/v2/check', [
                'ipAddress' => $ipAddress,
                'maxAgeInDays' => 90,
                'verbose' => true,
            ]);

            if (! $response->successful()) {
                Log::channel('security')->warning('AbuseIPDB API call failed', [
                    'ip' => $ipAddress,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json('data');
            if (! is_array($data)) {
                return null;
            }

            $score = is_numeric($data['abuseConfidenceScore'] ?? null) ? (int) $data['abuseConfidenceScore'] : 0;
            $totalReports = is_numeric($data['totalReports'] ?? null) ? (int) $data['totalReports'] : 0;

            $result = [
                'score' => $score,
                'is_malicious' => $score >= self::AUTO_BLOCK_THRESHOLD,
                'country' => is_string($data['countryCode'] ?? null) ? $data['countryCode'] : null,
                'usage_type' => is_string($data['usageType'] ?? null) ? $data['usageType'] : null,
                'isp' => is_string($data['isp'] ?? null) ? $data['isp'] : null,
                'total_reports' => $totalReports,
            ];

            Cache::put($cacheKey, $result, self::CACHE_TTL_SECONDS);

            return $result;
        } catch (\Exception $e) {
            Log::channel('security')->error('AbuseIPDB check failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Report a malicious IP to AbuseIPDB.
     *
     * @param  array<int>  $categories  AbuseIPDB category IDs
     */
    public function reportIp(string $ipAddress, string $comment, array $categories = [15]): bool
    {
        $apiKey = $this->getApiKey();
        if ($apiKey === null) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->post('https://api.abuseipdb.com/api/v2/report', [
                'ip' => $ipAddress,
                'categories' => implode(',', $categories),
                'comment' => mb_substr($comment, 0, 1024),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::channel('security')->error('AbuseIPDB report failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if an IP should be auto-blocked based on threat intel.
     */
    public function shouldAutoBlock(string $ipAddress): bool
    {
        $result = $this->checkIp($ipAddress);

        return $result !== null && $result['is_malicious'];
    }

    /**
     * Get the auto-block confidence threshold.
     */
    public function getAutoBlockThreshold(): int
    {
        $setting = Setting::get('threat_intel_auto_block_threshold', self::AUTO_BLOCK_THRESHOLD);

        return is_numeric($setting) ? (int) $setting : self::AUTO_BLOCK_THRESHOLD;
    }

    /**
     * Get the AbuseIPDB API key from settings.
     */
    private function getApiKey(): ?string
    {
        $key = Setting::get('abuseipdb_api_key');

        return is_string($key) && $key !== '' ? $key : null;
    }
}
