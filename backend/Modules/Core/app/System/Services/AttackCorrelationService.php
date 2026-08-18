<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Security\Models\SecurityLog;

/**
 * Attack Correlation Service.
 * Analyzes security logs to detect multi-stage attacks,
 * distributed patterns, and assigns threat scores to IPs.
 */
class AttackCorrelationService
{
    /**
     * Attack stage weights for threat scoring.
     *
     * @var array<string, int>
     */
    private const EVENT_WEIGHTS = [
        'malicious_scanner_blocked' => 2,
        'malicious_extension_blocked' => 2,
        'waf_sql_injection' => 5,
        'waf_xss' => 4,
        'waf_path_traversal' => 4,
        'waf_command_injection' => 5,
        'login_failed' => 1,
        'ip_blocked_temp' => 3,
        'shield_failed' => 1,
        'suspicious_activity' => 2,
        'global_blacklist_blocked' => 5,
        'country_blocked' => 2,
    ];

    /**
     * Analyze recent security events and detect attack campaigns.
     *
     * @param  int  $hours  Lookback window in hours
     * @return array{high_risk_ips: array<array{ip: string, score: int, events: array<string, int>, first_seen: string, last_seen: string}>, campaigns: array<array{type: string, description: string, ips: array<string>, event_count: int}>, stats: array{total_events: int, unique_ips: int, high_risk_count: int}}
     */
    public function analyzeThreats(int $hours = 24): array
    {
        $since = now()->subHours($hours);

        // 1. Build threat scores per IP
        /** @var Collection<int, \stdClass> $ipEvents */
        $ipEvents = SecurityLog::where('created_at', '>=', $since)
            ->whereIn('event_type', array_keys(self::EVENT_WEIGHTS))
            ->whereNotNull('ip_address')
            ->select('ip_address', 'event_type', DB::raw('COUNT(*) as event_count'), DB::raw('MIN(created_at) as first_seen'), DB::raw('MAX(created_at) as last_seen'))
            ->groupBy('ip_address', 'event_type')
            ->get();

        /** @var array<string, array{score: int, events: array<string, int>, first_seen: string, last_seen: string}> $ipScores */
        $ipScores = [];

        foreach ($ipEvents as $row) {
            /** @var string $ip */
            $ip = $row->ip_address;
            if ($ip === '') {
                continue;
            }

            $eventCount = (int) $row->event_count;
            $firstSeen = (string) $row->first_seen;
            $lastSeen = (string) $row->last_seen;

            if (! isset($ipScores[$ip])) {
                $ipScores[$ip] = ['score' => 0, 'events' => [], 'first_seen' => $firstSeen, 'last_seen' => $lastSeen];
            }

            $weight = self::EVENT_WEIGHTS[$row->event_type] ?? 1;
            $ipScores[$ip]['score'] += $weight * $eventCount;
            $ipScores[$ip]['events'][$row->event_type] = $eventCount;

            if ($firstSeen < $ipScores[$ip]['first_seen']) {
                $ipScores[$ip]['first_seen'] = $firstSeen;
            }
            if ($lastSeen > $ipScores[$ip]['last_seen']) {
                $ipScores[$ip]['last_seen'] = $lastSeen;
            }
        }

        // Sort by score descending
        uasort($ipScores, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        // 2. Identify high-risk IPs (score >= 10)
        $highRiskIps = [];
        foreach ($ipScores as $ip => $data) {
            if ($data['score'] >= 10) {
                $highRiskIps[] = [
                    'ip' => $ip,
                    'score' => $data['score'],
                    'events' => $data['events'],
                    'first_seen' => $data['first_seen'],
                    'last_seen' => $data['last_seen'],
                ];
            }
        }

        // 3. Detect attack campaigns
        $campaigns = $this->detectCampaigns($since);

        $totalEventsRaw = $ipEvents->sum('event_count');
        $totalEvents = is_numeric($totalEventsRaw) ? (int) $totalEventsRaw : 0;

        return [
            'high_risk_ips' => array_slice($highRiskIps, 0, 50),
            'campaigns' => $campaigns,
            'stats' => [
                'total_events' => $totalEvents,
                'unique_ips' => count($ipScores),
                'high_risk_count' => count($highRiskIps),
            ],
        ];
    }

    /**
     * Detect attack campaign patterns.
     *
     * @return array<array{type: string, description: string, ips: array<string>, event_count: int}>
     */
    private function detectCampaigns(Carbon $since): array
    {
        $campaigns = [];

        // Campaign 1: Distributed brute force (many IPs targeting login)
        $bruteForceIps = SecurityLog::where('event_type', 'login_failed')
            ->where('created_at', '>=', $since)
            ->whereNotNull('ip_address')
            ->select('ip_address', DB::raw('COUNT(*) as attempts'))
            ->groupBy('ip_address')
            ->having(DB::raw('COUNT(*)'), '>=', 3)
            ->pluck('ip_address')
            ->filter(fn ($ip): bool => is_string($ip))
            ->values()
            ->all();

        if (count($bruteForceIps) >= 5) {
            $campaigns[] = [
                'type' => 'distributed_brute_force',
                'description' => count($bruteForceIps).' IPs attempting brute force login attacks',
                'ips' => array_slice($bruteForceIps, 0, 20),
                'event_count' => count($bruteForceIps),
            ];
        }

        // Campaign 2: Multi-stage attack (scan → exploit attempt)
        $multiStageIps = SecurityLog::where('created_at', '>=', $since)
            ->whereIn('event_type', ['malicious_scanner_blocked', 'malicious_extension_blocked'])
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->unique()
            ->filter(fn ($ip): bool => is_string($ip));

        $exploitIps = SecurityLog::where('created_at', '>=', $since)
            ->whereIn('event_type', ['waf_sql_injection', 'waf_xss', 'waf_command_injection', 'login_failed'])
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->unique()
            ->filter(fn ($ip): bool => is_string($ip));

        $multiStageAttackers = $multiStageIps->intersect($exploitIps)->values()->all();

        if (count($multiStageAttackers) >= 2) {
            $campaigns[] = [
                'type' => 'multi_stage_attack',
                'description' => count($multiStageAttackers).' IPs performing scan-then-exploit pattern',
                'ips' => array_slice($multiStageAttackers, 0, 20),
                'event_count' => count($multiStageAttackers),
            ];
        }

        // Campaign 3: WAF evasion attempts (many different attack types from same IP)
        $diverseAttackers = SecurityLog::where('created_at', '>=', $since)
            ->whereIn('event_type', ['waf_sql_injection', 'waf_xss', 'waf_path_traversal', 'waf_command_injection'])
            ->whereNotNull('ip_address')
            ->select('ip_address', DB::raw('COUNT(DISTINCT event_type) as attack_types'))
            ->groupBy('ip_address')
            ->having(DB::raw('COUNT(DISTINCT event_type)'), '>=', 3)
            ->pluck('ip_address')
            ->filter(fn ($ip): bool => is_string($ip))
            ->values()
            ->all();

        if (! empty($diverseAttackers)) {
            $campaigns[] = [
                'type' => 'waf_evasion',
                'description' => count($diverseAttackers).' IPs using multiple attack vectors (possible WAF evasion)',
                'ips' => $diverseAttackers,
                'event_count' => count($diverseAttackers),
            ];
        }

        return $campaigns;
    }

    /**
     * Get threat score for a specific IP.
     */
    public function getIpThreatScore(string $ipAddress, int $hours = 24): int
    {
        $since = now()->subHours($hours);

        $events = SecurityLog::where('ip_address', $ipAddress)
            ->where('created_at', '>=', $since)
            ->whereIn('event_type', array_keys(self::EVENT_WEIGHTS))
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->get();

        $score = 0;
        foreach ($events as $event) {
            $weight = self::EVENT_WEIGHTS[$event->event_type] ?? 1;
            $countVal = $event->count;
            $count = is_numeric($countVal) ? (int) $countVal : 0;
            $score += $weight * $count;
        }

        return $score;
    }
}
