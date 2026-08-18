<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Modules\Core\Security\Models\SecurityLog;

/**
 * Anomaly Detection Service.
 * Tracks statistical anomalies per session/IP to identify suspicious
 * behavior before it triggers hard-coded security rules.
 */
class AnomalyDetectionService
{
    private const WINDOW_SECONDS = 3600; // 1 hour window

    private const MAX_SCORE = 100;

    /**
     * Track an event and return the updated suspicion score.
     */
    public function trackEvent(string $type, string $ip, ?string $sessionId = null): int
    {
        $key = $this->getCacheKey($ip, $sessionId);
        $cached = Cache::get($key, [
            'score' => 0,
            '404_count' => 0,
            'sensitive_access' => 0,
            'rate' => 0,
            'events' => [],
        ]);

        /** @var array{score: int, "404_count": int, sensitive_access: int, rate: int, events: array<mixed>} $data */
        $data = is_array($cached) ? $cached : [
            'score' => 0,
            '404_count' => 0,
            'sensitive_access' => 0,
            'rate' => 0,
            'events' => [],
        ];

        $scoreIncrease = $this->getScoreIncrease($type);
        $oldScore = (int) $data['score'];
        $data['score'] = min(self::MAX_SCORE, (int) $data['score'] + $scoreIncrease);
        $newScore = (int) $data['score'];

        if ($type === '404') {
            $data['404_count'] = (int) $data['404_count'] + 1;
        } elseif ($type === 'sensitive_path') {
            $data['sensitive_access'] = (int) $data['sensitive_access'] + 1;
        }

        // Log significant milestones to the database for Auto-Tune and UI visibility
        $this->logMilestones($ip, $oldScore, $newScore, $type);

        Cache::put($key, $data, now()->addSeconds(self::WINDOW_SECONDS));

        return $data['score'];
    }

    /**
     * Get the current suspicion score for an IP/Session.
     */
    public function getScore(string $ip, ?string $sessionId = null): int
    {
        $data = Cache::get($this->getCacheKey($ip, $sessionId));
        if (! is_array($data)) {
            return 0;
        }

        $score = $data['score'] ?? 0;

        return is_numeric($score) ? (int) $score : 0;
    }

    /**
     * Check if a request should be challenged based on anomaly score.
     */
    public function shouldChallenge(string $ip, ?string $sessionId = null): bool
    {
        $score = $this->getScore($ip, $sessionId);

        $threshold = config('security.anomaly.challenge_threshold', 40);

        return $score >= (is_numeric($threshold) ? (int) $threshold : 40);
    }

    /**
     * Check if a request should be blocked based on anomaly score.
     */
    public function shouldBlock(string $ip, ?string $sessionId = null): bool
    {
        $score = $this->getScore($ip, $sessionId);

        $threshold = config('security.anomaly.block_threshold', 80);

        return $score >= (is_numeric($threshold) ? (int) $threshold : 80);
    }

    /**
     * Reset scores for an IP (e.g. after manual review or whitelist).
     */
    public function reset(string $ip, ?string $sessionId = null): void
    {
        Cache::forget($this->getCacheKey($ip, $sessionId));
    }

    /**
     * Generate cache key for tracking.
     */
    private function getCacheKey(string $ip, ?string $sessionId): string
    {
        $identifier = $sessionId ?? $ip;

        return "security:anomaly:v1:{$identifier}";
    }

    /**
     * Log significant milestones to the security journal.
     */
    private function logMilestones(string $ip, int $oldScore, int $newScore, string $type): void
    {
        $thresholds = [25, 50, 75];
        foreach ($thresholds as $t) {
            if ($oldScore < $t && $newScore >= $t) {
                SecurityLog::log(
                    'suspicious_behavior',
                    null,
                    $ip,
                    "Behavioral anomaly threshold reached: {$t}/100 (Trigger: {$type})",
                    ['score' => $newScore, 'trigger' => $type]
                );

                Log::channel('security')->warning("Suspicious behavior threshold reached for {$ip}", [
                    'score' => $newScore,
                    'threshold' => $t,
                    'trigger' => $type,
                ]);
            }
        }

        // Special logging for 404 floods
        if ($type === '404' && $newScore % 20 === 0) {
            Log::channel('security')->info("Continued 404 scanning detected from {$ip}", [
                'score' => $newScore,
            ]);
        }
    }

    /**
     * Define scoring weight for different event types.
     */
    private function getScoreIncrease(string $type): int
    {
        return match ($type) {
            '404' => 5,                // Many 404s indicate scanning
            'sensitive_path' => 15,    // Accessing .env, config, etc.
            'waf_violation' => 20,     // Minor WAF hits
            'failed_login' => 10,      // Failed login attempts
            'suspicious_ua' => 10,     // Weird user agents
            'missing_headers' => 5,    // Missing standard headers
            default => 2,
        };
    }
}
