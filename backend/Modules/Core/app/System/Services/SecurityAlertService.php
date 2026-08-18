<?php

namespace Modules\Core\System\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\LoginHistory;
use Modules\Core\System\Models\Setting;

class SecurityAlertService
{
    /**
     * Alert thresholds
     */
    protected int $failedLoginThreshold = 5;

    protected int $blockedIpThreshold = 3;

    protected int $suspiciousIpThreshold = 10;

    protected int $checkWindowMinutes = 60;

    public function __construct()
    {
        $this->failedLoginThreshold = $this->resolveIntSetting(
            'security_alert_failed_login_threshold',
            'SECURITY_ALERT_FAILED_LOGIN_THRESHOLD',
            ['production' => 5, 'staging' => 8, 'local' => 15, 'testing' => 15],
            3
        );
        $this->blockedIpThreshold = $this->resolveIntSetting(
            'security_alert_blocked_ip_threshold',
            'SECURITY_ALERT_BLOCKED_IP_THRESHOLD',
            ['production' => 3, 'staging' => 4, 'local' => 6, 'testing' => 6],
            1
        );
        $this->suspiciousIpThreshold = $this->resolveIntSetting(
            'security_alert_suspicious_ip_threshold',
            'SECURITY_ALERT_SUSPICIOUS_IP_THRESHOLD',
            ['production' => 10, 'staging' => 14, 'local' => 25, 'testing' => 25],
            3
        );
        $this->checkWindowMinutes = $this->resolveIntSetting(
            'security_alert_window_minutes',
            'SECURITY_ALERT_WINDOW_MINUTES',
            ['production' => 60, 'staging' => 90, 'local' => 120, 'testing' => 120],
            5
        );
    }

    /**
     * Resolve environment-aware threshold in this order:
     * 1) DB setting by env suffix (`<key>_<env>`)
     * 2) Base DB setting (`<key>`)
     * 3) Config override (`security.alert_env.<ENV_VAR_NAME>`)
     * 4) Environment profile default map
     *
     * @param  array<string, int>  $defaultsByEnv
     */
    private function resolveIntSetting(string $baseKey, string $envVar, array $defaultsByEnv, int $min): int
    {
        $env = app()->environment();
        $settingEnvKey = $baseKey.'_'.$env;

        $envScoped = Setting::get($settingEnvKey);
        if (is_numeric($envScoped)) {
            return max($min, (int) $envScoped);
        }

        $baseSetting = Setting::get($baseKey);
        if (is_numeric($baseSetting)) {
            return max($min, (int) $baseSetting);
        }

        $envValue = config('security.alert_env.'.$envVar);
        if (is_numeric($envValue)) {
            return max($min, (int) $envValue);
        }

        $default = $defaultsByEnv[$env] ?? ($defaultsByEnv['production'] ?? $min);

        return max($min, (int) $default);
    }

    /**
     * Get all security alerts
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAlerts(): array
    {
        return Cache::remember('security_alerts', 60, function (): array {
            /** @var array<int, array<string, mixed>> $alerts */
            $alerts = [];
            $since = Carbon::now()->subMinutes($this->checkWindowMinutes);

            // Check for multiple failed logins
            $failedLogins = $this->getFailedLoginAlerts($since);
            $alerts = array_merge($alerts, $failedLogins);

            // Check for blocked IPs
            $blockedIps = $this->getBlockedIpAlerts($since);
            $alerts = array_merge($alerts, $blockedIps);

            // Check for suspicious activity patterns
            $suspicious = $this->getSuspiciousActivityAlerts($since);
            $alerts = array_merge($alerts, $suspicious);

            // Sort by severity and time
            usort($alerts, function (array $a, array $b): int {
                $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
                $aSev = is_string($a['severity'] ?? null) ? $a['severity'] : 'info';
                $bSev = is_string($b['severity'] ?? null) ? $b['severity'] : 'info';

                $severityDiff = ($severityOrder[$aSev] ?? 3) - ($severityOrder[$bSev] ?? 3);
                if ($severityDiff !== 0) {
                    return $severityDiff;
                }

                $aTime = is_string($a['timestamp'] ?? null) ? $a['timestamp'] : now()->toDateTimeString();
                $bTime = is_string($b['timestamp'] ?? null) ? $b['timestamp'] : now()->toDateTimeString();

                return strtotime($bTime) - strtotime($aTime);
            });

            return array_slice($alerts, 0, 20); // Max 20 alerts
        });
    }

    /**
     * Get count of unread/active alerts
     */
    public function getAlertCount(): int
    {
        $alerts = $this->getAlerts();

        return count(array_filter($alerts, fn (array $a): bool => $a['severity'] !== 'info'));
    }

    /**
     * Check for failed login attempts
     * Check for failed login attempts
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getFailedLoginAlerts(Carbon $since): array
    {
        $alerts = [];

        try {
            // Group failed logins by IP
            /** @var Collection<int, SecurityLog> $failedByIp */
            $failedByIp = SecurityLog::where('created_at', '>=', $since)
                ->where('event_type', 'login_failed')
                ->selectRaw('ip_address, COUNT(*) as count')
                ->groupBy('ip_address')
                ->having('count', '>=', $this->failedLoginThreshold)
                ->get();

            foreach ($failedByIp as $record) {
                $count = is_scalar($c = $record->getAttribute('count')) ? (int) $c : 0;
                $ipAddress = is_scalar($ip = $record->getAttribute('ip_address')) ? (string) $ip : 'unknown';
                $alerts[] = [
                    'id' => 'failed_login_'.md5($ipAddress),
                    'type' => 'failed_login',
                    'severity' => $count >= 10 ? 'critical' : 'warning',
                    'title' => 'Multiple Failed Logins',
                    'message' => "IP {$ipAddress} has {$count} failed login attempts in the last hour",
                    'ip_address' => $ipAddress,
                    'count' => $count,
                    'timestamp' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get failed login alerts: '.$e->getMessage());
        }

        return $alerts;
    }

    /**
     * Check for recently blocked IPs
     * Check for recently blocked IPs
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getBlockedIpAlerts(Carbon $since): array
    {
        $alerts = [];

        try {
            /** @var Collection<int, SecurityLog> $blockedIps */
            $blockedIps = SecurityLog::where('created_at', '>=', $since)
                ->whereIn('event_type', ['ip_blocked', 'ip_blocked_temp', 'ip_blocked_permanent'])
                ->selectRaw('ip_address, COUNT(*) as count, MAX(created_at) as latest')
                ->groupBy('ip_address')
                ->havingRaw('COUNT(*) >= ?', [$this->blockedIpThreshold])
                ->orderByDesc('latest')
                ->limit(20)
                ->get();

            foreach ($blockedIps as $log) {
                $count = is_numeric($log->getAttribute('count')) ? (int) $log->getAttribute('count') : 0;
                $latestRaw = $log->getAttribute('latest');
                $alerts[] = [
                    'id' => 'blocked_ip_'.md5((string) ($log->ip_address ?? 'unknown')),
                    'type' => 'ip_blocked',
                    'severity' => $count >= ($this->blockedIpThreshold * 2) ? 'critical' : 'warning',
                    'title' => 'IP Blocked',
                    'message' => "IP {$log->ip_address} blocked {$count} time(s) in the alert window",
                    'ip_address' => $log->ip_address,
                    'count' => $count,
                    'timestamp' => is_string($latestRaw) ? Carbon::parse($latestRaw)->toISOString() : now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get blocked IP alerts: '.$e->getMessage());
        }

        return $alerts;
    }

    /**
     * Check for suspicious activity patterns
     * Check for suspicious activity patterns
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getSuspiciousActivityAlerts(Carbon $since): array
    {
        $alerts = [];

        try {
            // Check for unusual login patterns (same user from multiple IPs)
            /** @var Collection<int, LoginHistory> $multipleIpLogins */
            $multipleIpLogins = LoginHistory::where('created_at', '>=', $since)
                ->where('status', 'success')
                ->selectRaw('user_id, COUNT(DISTINCT ip_address) as ip_count')
                ->groupBy('user_id')
                ->having('ip_count', '>=', 3)
                ->with('user:id,name,email')
                ->get();

            foreach ($multipleIpLogins as $record) {
                $user = $record->user;
                if ($user) {
                    $ipCount = is_scalar($ipc = $record->getAttribute('ip_count')) ? (int) $ipc : 0;
                    $alerts[] = [
                        'id' => 'multiple_ip_'.$record->user_id,
                        'type' => 'multiple_ip_login',
                        'severity' => 'info',
                        'title' => 'Multiple IP Login',
                        'message' => "User {$user->name} logged in from {$ipCount} different IPs",
                        'user_id' => $record->user_id,
                        'user_name' => $user->name,
                        'timestamp' => now()->toISOString(),
                    ];
                }
            }

            // Check for brute force attempts (many failed logins to same user)
            /** @var Collection<int, SecurityLog> $bruteForce */
            $bruteForce = LoginHistory::where('created_at', '>=', $since)
                ->where('status', 'failed')
                ->whereNotNull('user_id')
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->having('count', '>=', $this->failedLoginThreshold)
                ->get();

            foreach ($bruteForce as $record) {
                $count = is_scalar($c = $record->getAttribute('count')) ? (int) $c : 0;
                $alerts[] = [
                    'id' => 'brute_force_'.$record->user_id,
                    'type' => 'brute_force',
                    'severity' => 'critical',
                    'title' => 'Possible Brute Force Attack',
                    'message' => "User ID {$record->user_id} received {$count} failed login attempts",
                    'user_id' => $record->user_id,
                    'count' => $count,
                    'timestamp' => now()->toISOString(),
                ];
            }

            // Detect repeated permission denied bursts by IP (possible privilege probing).
            /** @var Collection<int, SecurityLog> $permissionDenied */
            $permissionDenied = SecurityLog::where('created_at', '>=', $since)
                ->where('event_type', 'permission_denied')
                ->selectRaw('ip_address, COUNT(*) as count')
                ->groupBy('ip_address')
                ->having('count', '>=', $this->suspiciousIpThreshold)
                ->get();

            foreach ($permissionDenied as $record) {
                $count = is_scalar($c = $record->getAttribute('count')) ? (int) $c : 0;
                $ipAddress = is_scalar($ip = $record->getAttribute('ip_address')) ? (string) $ip : 'unknown';
                $alerts[] = [
                    'id' => 'permission_probe_'.md5($ipAddress),
                    'type' => 'permission_probe',
                    'severity' => $count >= ($this->suspiciousIpThreshold * 2) ? 'critical' : 'warning',
                    'title' => 'Permission Probe Detected',
                    'message' => "IP {$ipAddress} generated {$count} forbidden access attempts",
                    'ip_address' => $ipAddress,
                    'count' => $count,
                    'timestamp' => now()->toISOString(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get suspicious activity alerts: '.$e->getMessage());
        }

        return $alerts;
    }

    /**
     * Clear cached alerts
     */
    public function clearCache(): void
    {
        Cache::forget('security_alerts');
    }
}
