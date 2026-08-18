<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Core\Security\Models\IpList;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\LoginHistory;
use Modules\Core\System\Models\User;
use Symfony\Component\HttpFoundation\Response;

class LoginHistoryController extends BaseApiController
{
    /**
     * Get all login history for admin
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = LoginHistory::with('user');

            // Filters
            if ($request->has('user_id') && $request->input('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->has('status') && $request->input('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('ip_address') && $request->input('ip_address')) {
                $query->where('ip_address', $request->input('ip_address'));
            }

            if ($request->has('date_from') && $request->input('date_from')) {
                $dateFromRaw = $request->input('date_from');
                $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
                if ($dateFrom) {
                    $query->whereDate('login_at', '>=', $dateFrom);
                }
            }

            if ($request->has('date_to') && $request->input('date_to')) {
                $dateToRaw = $request->input('date_to');
                $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
                if ($dateTo) {
                    $query->whereDate('login_at', '<=', $dateTo);
                }
            }

            // Pagination
            $perPageRaw = $request->input('per_page', 50);
            $perPageInt = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;
            $perPage = min(max($perPageInt, 10), 500);
            $logs = $query->latest('login_at')->paginate($perPage);

            return $this->paginated($logs, 'Login history retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Login history index error: '.$e->getMessage());

            return $this->success([], 'Login history retrieved successfully');
        }
    }

    /**
     * Get login history statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_logins' => LoginHistory::where('status', 'success')->count(),
                'failed_logins' => LoginHistory::where('status', 'failed')->count(),
                'today_logins' => LoginHistory::where('status', 'success')
                    ->whereDate('login_at', today())->count(),
                'unique_ips_today' => LoginHistory::whereDate('login_at', today())
                    ->distinct('ip_address')->count('ip_address'),
                'active_sessions' => LoginHistory::whereNull('logout_at')
                    ->where('status', 'success')->count(),
                'suspicious_count' => $this->countSuspicious(),
            ];

            return $this->success($stats, 'Login history statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Login history statistics error: '.$e->getMessage());

            return $this->success([
                'total_logins' => 0,
                'failed_logins' => 0,
                'today_logins' => 0,
                'unique_ips_today' => 0,
                'active_sessions' => 0,
                'suspicious_count' => 0,
            ], 'Login history statistics retrieved successfully');
        }
    }

    /**
     * Get suspicious login activities.
     */
    public function suspicious(): JsonResponse
    {
        try {
            $alerts = [];
            $whitelistedIps = $this->getWhitelistedIps();

            $failedAttemptsQuery = LoginHistory::where('status', 'failed')
                ->where('login_at', '>=', now()->subDay())
                ->selectRaw('user_id, ip_address, count(*) as fail_count')
                ->groupBy('user_id', 'ip_address')
                ->havingRaw('count(*) >= 3');
            if ($whitelistedIps !== []) {
                $failedAttemptsQuery->whereNotIn('ip_address', $whitelistedIps);
            }
            /** @var Collection<int, object{user_id: int|null, ip_address: string|null, fail_count: int}> $failedAttempts */
            $failedAttempts = $failedAttemptsQuery->get();

            foreach ($failedAttempts as $attempt) {
                $user = $attempt->user_id ? User::find($attempt->user_id) : null;
                /** @var object{user_id: int|null, ip_address: string|null, fail_count: int} $attempt */
                $alerts[] = [
                    'type' => 'brute_force',
                    'severity' => 'high',
                    'user' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null,
                    'ip_address' => $attempt->ip_address,
                    'details' => (int) $attempt->fail_count.' failed attempts in last 24h',
                    'count' => (int) $attempt->fail_count,
                ];
            }

            // 2. New IPs for existing users (never seen before)
            $recentLoginsQuery = LoginHistory::where('status', 'success')
                ->where('login_at', '>=', now()->subDay())
                ->whereNotNull('user_id')
                ->whereNotNull('ip_address');
            if ($whitelistedIps !== []) {
                $recentLoginsQuery->whereNotIn('ip_address', $whitelistedIps);
            }
            $recentLogins = $recentLoginsQuery->get(['user_id', 'ip_address', 'login_at']);

            foreach ($recentLogins as $login) {
                $hasPreviousLogins = LoginHistory::where('user_id', $login->user_id)
                    ->where('login_at', '<', $login->login_at)
                    ->where('status', 'success')
                    ->exists();

                $previouslyUsed = LoginHistory::where('user_id', $login->user_id)
                    ->where('ip_address', $login->ip_address)
                    ->where('login_at', '<', $login->login_at)
                    ->where('status', 'success')
                    ->exists();

                // Only flag if they have successfully logged in before, but NOT from this IP.
                // This prevents flagging a brand new user's very first login.
                if ($hasPreviousLogins && ! $previouslyUsed) {
                    $user = User::find($login->user_id);
                    if ($user) {
                        $alerts[] = [
                            'type' => 'new_ip',
                            'severity' => 'medium',
                            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                            'ip_address' => $login->ip_address,
                            'details' => 'First login from this IP address',
                            'detected_at' => $login->login_at,
                        ];
                    }
                }
            }

            // 3. Same IP used by multiple different users (account sharing/compromise)
            $sharedIpsQuery = LoginHistory::where('status', 'success')
                ->where('login_at', '>=', now()->subWeek())
                ->whereNotNull('user_id')
                ->selectRaw('ip_address, count(distinct user_id) as user_count')
                ->groupBy('ip_address')
                ->havingRaw('count(distinct user_id) >= 3');
            if ($whitelistedIps !== []) {
                $sharedIpsQuery->whereNotIn('ip_address', $whitelistedIps);
            }
            /** @var Collection<int, object{ip_address: string|null, user_count: int}> $sharedIps */
            $sharedIps = $sharedIpsQuery->get();

            foreach ($sharedIps as $shared) {
                $alerts[] = [
                    'type' => 'shared_ip',
                    'severity' => 'low',
                    'ip_address' => $shared->ip_address,
                    'details' => (int) $shared->user_count.' different users from same IP this week',
                    'count' => (int) $shared->user_count,
                ];
            }

            // Sort by severity (high first)
            $severityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            usort($alerts, fn (array $a, array $b) => $severityOrder[$a['severity']] <=> ($severityOrder[$b['severity']]));

            return $this->success([
                'alerts' => $alerts,
                'total' => count($alerts),
            ], 'Suspicious login activities retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Suspicious login detection error: '.$e->getMessage());

            return $this->success(['alerts' => [], 'total' => 0], 'Suspicious login activities retrieved successfully');
        }
    }

    /**
     * Count total suspicious alerts for statistics.
     */
    private function countSuspicious(): int
    {
        $count = 0;
        $whitelistedIps = $this->getWhitelistedIps();

        // Brute force attempts
        $bruteForceQuery = LoginHistory::where('status', 'failed')
            ->where('login_at', '>=', now()->subDay())
            ->select('user_id', 'ip_address')
            ->groupBy('user_id', 'ip_address')
            ->havingRaw('count(*) >= 3');
        if ($whitelistedIps !== []) {
            $bruteForceQuery->whereNotIn('ip_address', $whitelistedIps);
        }
        $count += count($bruteForceQuery->get());

        // New IPs (approximate — count distinct user-IP combos today not seen before)
        $todayNewIpsQuery = LoginHistory::where('status', 'success')
            ->where('login_at', '>=', now()->subDay())
            ->whereNotNull('user_id')
            ->whereNotNull('ip_address');
        if ($whitelistedIps !== []) {
            $todayNewIpsQuery->whereNotIn('ip_address', $whitelistedIps);
        }
        $todayNewIps = $todayNewIpsQuery->get(['user_id', 'ip_address', 'login_at']);

        foreach ($todayNewIps as $login) {
            $hasPreviousLogins = LoginHistory::where('user_id', $login->user_id)
                ->where('login_at', '<', $login->login_at)
                ->where('status', 'success')
                ->exists();

            $existed = LoginHistory::where('user_id', $login->user_id)
                ->where('ip_address', $login->ip_address)
                ->where('login_at', '<', $login->login_at)
                ->where('status', 'success')
                ->exists();

            if ($hasPreviousLogins && ! $existed) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private function getWhitelistedIps(): array
    {
        /** @var array<int, string> $ips */
        $ips = IpList::query()
            ->whitelist()
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->filter(static fn ($ip): bool => is_string($ip) && $ip !== '')
            ->values()
            ->all();

        return $ips;
    }

    /**
     * Export login history to CSV
     */
    public function export(Request $request): Response
    {
        try {
            $query = LoginHistory::with('user');

            // Apply same filters as index
            if ($request->has('user_id') && $request->input('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->has('status') && $request->input('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('date_from') && $request->input('date_from')) {
                $dateFromRaw = $request->input('date_from');
                $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
                if ($dateFrom) {
                    $query->whereDate('login_at', '>=', $dateFrom);
                }
            }

            if ($request->has('date_to') && $request->input('date_to')) {
                $dateToRaw = $request->input('date_to');
                $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
                if ($dateTo) {
                    $query->whereDate('login_at', '<=', $dateTo);
                }
            }

            $logs = $query->latest('login_at')->limit(10000)->get();

            // Generate CSV
            $csv = "ID,User,Email,Status,IP Address,Login At,Logout At,Duration,Failure Reason\n";
            foreach ($logs as $log) {
                $duration = '';
                if ($log->login_at && $log->logout_at) {
                    $minutes = $log->login_at->diffInMinutes($log->logout_at);
                    $duration = $minutes.' min';
                }

                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                    $log->id,
                    $log->user->name ?? 'Unknown',
                    $log->user->email ?? '',
                    $log->status,
                    $log->ip_address ?? '',
                    $log->login_at?->format('Y-m-d H:i:s') ?? '',
                    $log->logout_at?->format('Y-m-d H:i:s') ?? '',
                    $duration,
                    str_replace('"', '""', $log->failure_reason ?? '')
                );
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="login-history-'.now()->format('Y-m-d').'.csv"',
            ]);
        } catch (\Exception $e) {
            Log::error('Login history export error: '.$e->getMessage());

            return $this->error('Failed to export login history', 500);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        try {
            $retainDaysRaw = $request->input('retain_days');
            $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : null;

            if ($retainDays) {
                $count = LoginHistory::where('login_at', '<', now()->subDays($retainDays))->delete();
                $countInt = is_numeric($count) ? (int) $count : 0;

                return $this->success(null, "Cleared {$countInt} login history records older than {$retainDays} days");
            }

            LoginHistory::truncate();

            return $this->success(null, 'All login history cleared successfully');
        } catch (\Exception $e) {
            Log::error('Login history clear error: '.$e->getMessage());

            return $this->error('Failed to clear login history', 500);
        }
    }
}
