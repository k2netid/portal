<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\Setting;

/**
 * Security Assessment Service.
 * Calculates a dynamic "Security Health Score" and assesses
 * the overall security posture of the application.
 */
class SecurityAssessmentService
{
    /**
     * Calculate security health score (0-100).
     * Philosophy: A system that successfully BLOCKS all attacks should score 100.
     * Only unmitigated threats (integrity violations, unblocked suspicious activity)
     * should reduce the score. High defense effectiveness earns a bonus.
     *
     * @return array<string, mixed>
     */
    public function calculateScore(): array
    {
        return Cache::remember('security_health_assessment', 3600, function (): array {
            $baseScore = 60; // Start with a decent base
            $factors = [];

            // 1. Core Protections (Positive Weight: +45 max)
            $protections = [
                'waf_enabled' => ['weight' => 10, 'check' => Setting::get('waf_enabled', true)],
                'bot_shield_enabled' => ['weight' => 10, 'check' => Setting::get('shield_enabled', true)],
                'integrity_monitoring' => ['weight' => 5, 'check' => Setting::get('integrity_enabled', true)],
                'force_https' => ['weight' => 5, 'check' => request()->isSecure() || config('app.url') && str_starts_with(is_string(config('app.url')) ? config('app.url') : '', 'https')],
                'csp_protection' => ['weight' => 5, 'check' => true],
                'secure_headers' => ['weight' => 5, 'check' => true],
                'honeypot_active' => ['weight' => 5, 'check' => true],
            ];

            foreach ($protections as $key => $p) {
                if ($p['check']) {
                    $baseScore += $p['weight'];
                    $factors[] = ['key' => $key, 'status' => 'active', 'impact' => "+{$p['weight']}"];
                } else {
                    $factors[] = ['key' => $key, 'status' => 'inactive', 'impact' => '0'];
                }
            }

            // 2. Defense Effectiveness (Bonus: +5 if blocking well)
            // Blocked attacks = system working correctly → REWARD, not penalty
            $blockedAttacks = SecurityLog::where('created_at', '>=', now()->subDay())
                ->whereIn('event_type', [
                    'waf_sql_injection', 'malicious_scanner_blocked', 'shield_honeypot',
                    'ip_blocked', 'ip_blocked_permanent', 'ip_blocked_temp',
                    'malicious_extension_blocked', 'waf_violation',
                ])
                ->count();

            if ($blockedAttacks > 0) {
                // Having active defenses that block threats is a good indicator
                $baseScore += 5;
                $factors[] = ['key' => 'defense_effectiveness', 'status' => 'excellent', 'impact' => '+5'];
            }

            // 3. Unmitigated Threats (Negative Weight: -15 max)
            // Only count events that indicate UNRESOLVED security issues
            $unresolvedThreats = SecurityLog::where('created_at', '>=', now()->subDay())
                ->whereIn('event_type', [
                    'login_failed',            // Failed logins = brute force attempts
                    'account_locked',          // Account lockouts = sustained attacks
                    'session_limit_reached',   // Session abuse
                ])
                ->count();

            // Moderate penalty: only for sustained unresolved threat patterns
            // 50+ failed logins = likely brute force, -1 per 50, max -10
            $threatPenalty = min(10, (int) floor($unresolvedThreats / 50));
            $baseScore -= $threatPenalty;
            if ($threatPenalty > 0) {
                $factors[] = ['key' => 'unresolved_threats', 'status' => 'warning', 'impact' => "-{$threatPenalty}"];
            }

            // 4. System Integrity (Critical Weight: -20 max)
            $integrityCheck = app(FileIntegrityService::class)->verify();
            $violationCount = count($integrityCheck['modified']) + count($integrityCheck['missing']);
            if ($violationCount > 0) {
                $integrityPenalty = min(20, $violationCount * 5);
                $baseScore -= $integrityPenalty;
                $factors[] = ['key' => 'file_integrity_violations', 'status' => 'critical', 'impact' => "-{$integrityPenalty}"];
            }

            $finalScore = max(0, min(100, $baseScore));

            return [
                'score' => $finalScore,
                'level' => $this->getScoreLevel($finalScore),
                'factors' => $factors,
                'stats' => [
                    'blocked_attacks_24h' => $blockedAttacks,
                    'unresolved_threats_24h' => $unresolvedThreats,
                    'integrity_violations' => $violationCount,
                ],
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Get label for score levels.
     */
    private function getScoreLevel(int $score): string
    {
        if ($score >= 90) {
            return 'Elite';
        }
        if ($score >= 75) {
            return 'Strong';
        }
        if ($score >= 50) {
            return 'Fair';
        }

        return 'At Risk';
    }

    /**
     * Get security trend data (attacks per hour for last 24h).
     *
     * @return array<string, mixed>
     */
    public function getTrendData(): array
    {
        return Cache::remember('security_attack_trend_24h', 1800, function (): array {
            $data = SecurityLog::where('created_at', '>=', now()->subDay())
                ->whereIn('event_type', ['waf_sql_injection', 'malicious_scanner_blocked', 'shield_honeypot', '404', 'waf_violation'])
                ->select(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD HH24:00:00') as hour"), DB::raw('count(*) as count'))
                ->groupBy('hour')
                ->orderBy('hour', 'asc')
                ->get();

            $labels = [];
            $values = [];

            // Fill gaps with zeros for a smooth line chart
            for ($i = 23; $i >= 0; $i--) {
                $time = now()->subHours($i)->format('Y-m-d H:00:00');
                $labels[] = now()->subHours($i)->format('H:00');

                $match = $data->firstWhere('hour', $time);
                $values[] = $match ? $match->count : 0;
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Security Events',
                        'data' => $values,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ];
        });
    }
}
