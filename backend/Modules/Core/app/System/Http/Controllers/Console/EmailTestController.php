<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;

class EmailTestController extends BaseApiController
{
    /**
     * Test SMTP connection
     * Attempts to connect to the SMTP server using current configuration
     */
    public function testConnection(Request $request): JsonResponse
    {
        try {
            $config = $this->getEmailConfig($request);

            // Validate required config
            if (empty($config['host']) || empty($config['port'])) {
                return $this->error(
                    'SMTP host and port are required',
                    422,
                    [],
                    'INVALID_CONFIG'
                );
            }

            // Test connection by attempting to connect to SMTP server
            // We'll use fsockopen to test basic connectivity
            $this->updateMailConfig($config);

            // Test basic socket connection
            $timeout = 5;
            $hostRaw = $config['host'];
            $host = is_string($hostRaw) ? $hostRaw : '';
            $portRaw = $config['port'];
            $port = is_numeric($portRaw) ? (int) $portRaw : 25;
            $connection = @fsockopen(
                $host,
                $port,
                $errno,
                $errstr,
                $timeout
            );

            if (! $connection) {
                throw new \Exception("Cannot connect to {$host}:{$port} - {$errstr} ({$errno})");
            }

            fclose($connection);
            $testResult = true;

            // Cache the successful status for the dashboard
            Cache::put('email_smtp_status', 'active', now()->addHours(24));

            $encryption = isset($config['encryption']) && is_string($config['encryption']) ? $config['encryption'] : 'none';

            return $this->success([
                'connected' => $testResult,
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption,
            ], 'SMTP connection test completed');
        } catch (\Exception $e) {
            // Cache the failed status for the dashboard
            Cache::put('email_smtp_status', 'error', now()->addHours(1));

            return $this->error(
                'Failed to connect to SMTP server: '.$e->getMessage(),
                500,
                [],
                'SMTP_CONNECTION_FAILED',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Send test email
     * Sends a test email to the specified address
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $config = $this->getEmailConfig($request);

            // Temporarily update mail config
            $this->updateMailConfig($config);

            $toRaw = $validated['to'];
            $to = is_string($toRaw) ? $toRaw : '';
            $appName = config('app.name');
            $appNameStr = is_string($appName) ? $appName : 'App';
            $subjectRaw = $validated['subject'] ?? 'Test Email from '.$appNameStr;
            $subject = is_string($subjectRaw) ? $subjectRaw : 'Test Email';
            $messageRaw = $validated['message'] ?? 'This is a test email sent from the Jejakawan email testing tool.';
            $message = is_string($messageRaw) ? $messageRaw : 'Test Message';

            // Send email
            Mail::raw($message, function (Message $mail) use ($to, $subject, $config): void {
                $fromAddressRaw = $config['from_address'] ?? null;
                $fromAddress = is_string($fromAddressRaw) ? $fromAddressRaw : '';
                $fromNameRaw = $config['from_name'] ?? null;
                $fromName = is_string($fromNameRaw) ? $fromNameRaw : null;

                $mail->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);
            });

            // Log the test email
            $this->logEmailSent([
                'to' => $to,
                'subject' => $subject,
                'type' => 'test',
                'status' => 'sent',
            ]);

            // Cache the successful status for the dashboard
            Cache::put('email_smtp_status', 'active', now()->addHours(24));

            return $this->success([
                'to' => $to,
                'subject' => $subject,
                'sent_at' => now()->toIso8601String(),
            ], 'Test email sent successfully');
        } catch (\Exception $e) {
            $logContext = [
                'to' => $validated['to'] ?? null,
                'error' => $e->getMessage(),
            ];
            /** @var array<string, mixed> $logContext */
            Log::error('Test email failed', $logContext);

            return $this->error(
                'Failed to send test email: '.$e->getMessage(),
                500,
                [],
                'EMAIL_SEND_FAILED',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Get email queue status
     * Returns information about pending email jobs in the queue
     */
    public function getQueueStatus(): JsonResponse
    {
        try {
            $queueConnectionRaw = config('queue.default');
            $queueConnection = is_string($queueConnectionRaw) ? $queueConnectionRaw : 'sync';
            $queueDriver = config("queue.connections.{$queueConnection}.driver");

            $status = [
                'driver' => $queueDriver,
                'connection' => $queueConnection,
                'pending_jobs' => 0,
                'failed_jobs' => 0,
            ];

            // Get queue stats based on driver
            if ($queueDriver === 'database') {
                $status['pending_jobs'] = DB::table('jobs')->count();
                $status['failed_jobs'] = DB::table('failed_jobs')->count();
            } elseif ($queueDriver === 'redis') {
                // Try to get queue length from Redis
                try {
                    $redis = app('redis');
                    $status['pending_jobs'] = $redis->llen('queues:default');
                } catch (\Exception) {
                    // Redis not available or queue name different
                    $status['pending_jobs'] = 'unknown';
                }
            }

            // Get mail queue stats if using mail queue
            $mailQueueCount = Cache::get('email_queue_count', 0);

            return $this->success($status, 'Queue status retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get queue status', 'Email queue status');
        }
    }

    /**
     * Get recent email logs
     * Returns recent email sending logs (from cache or log files)
     */
    public function recentJournal(Request $request): JsonResponse
    {
        try {
            $limitRaw = $request->get('limit', 10);
            $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;
            $limit = min(max(1, $limit), 50); // Between 1 and 50

            // Get from cache (stored when emails are sent)
            $logsRaw = Cache::get('email_logs', []);
            $logs = is_array($logsRaw) ? $logsRaw : [];

            // Sort by timestamp descending
            usort($logs, function (mixed $a, mixed $b): int {
                if (! is_array($a) || ! is_array($b)) {
                    return 0;
                }
                $aAt = isset($a['sent_at']) && is_string($a['sent_at']) ? strtotime($a['sent_at']) : 0;
                $bAt = isset($b['sent_at']) && is_string($b['sent_at']) ? strtotime($b['sent_at']) : 0;

                return $bAt - $aAt;
            });

            // Limit results
            $logs = array_slice($logs, 0, $limit);

            $totalCountRaw = Cache::get('email_logs', []);
            $totalCount = is_countable($totalCountRaw) ? count($totalCountRaw) : 0;

            return $this->success([
                'logs' => $logs,
                'total' => $totalCount,
            ], 'Recent email logs retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get email logs', 'Email logs');
        }
    }

    /**
     * Validate email configuration
     * Validates the current email configuration settings
     */
    public function validateConfig(Request $request): JsonResponse
    {
        try {
            $config = $this->getEmailConfig($request);

            $errors = [];
            $warnings = [];

            // Required fields
            if (empty($config['host'])) {
                $errors[] = 'SMTP host is required';
            }

            if (empty($config['port'])) {
                $errors[] = 'SMTP port is required';
            }

            if (empty($config['from_address'])) {
                $errors[] = 'From address is required';
            } elseif (! filter_var($config['from_address'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'From address is not a valid email';
            }

            // Warnings
            if (empty($config['username'])) {
                $warnings[] = 'SMTP username is not set (may be required for authentication)';
            }

            if (empty($config['password'])) {
                $warnings[] = 'SMTP password is not set (may be required for authentication)';
            }

            if (empty($config['encryption'])) {
                $warnings[] = 'SMTP encryption is not set (recommended: tls or ssl)';
            }

            $isValid = $errors === [];

            return $this->success([
                'valid' => $isValid,
                'errors' => $errors,
                'warnings' => $warnings,
                'config' => [
                    'host' => $config['host'] ?? null,
                    'port' => $config['port'] ?? null,
                    'encryption' => $config['encryption'] ?? null,
                    'from_address' => $config['from_address'] ?? null,
                    'from_name' => $config['from_name'] ?? null,
                    'has_username' => ! empty($config['username']),
                    'has_password' => ! empty($config['password']),
                ],
            ], $isValid ? 'Email configuration is valid' : 'Email configuration has errors');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to validate email configuration', 'Email config validation');
        }
    }

    /**
     * Get email configuration from request or settings
     *
     * @return array<string, mixed>
     */
    private function getEmailConfig(Request $request): array
    {
        // If config is provided in request, use it (for testing different configs)
        if ($request->has('config')) {
            $requestConfig = $request->get('config', []);
            if (! is_array($requestConfig)) {
                $requestConfig = [];
            }

            return array_merge([
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ], $requestConfig);
        }

        // Otherwise, get from settings or config
        $settings = Setting::getGroup('email');

        return [
            'host' => $settings['email.smtp_host'] ?? config('mail.mailers.smtp.host'),
            'port' => $settings['email.smtp_port'] ?? config('mail.mailers.smtp.port'),
            'username' => $settings['email.smtp_username'] ?? config('mail.mailers.smtp.username'),
            'password' => $settings['email.smtp_password'] ?? config('mail.mailers.smtp.password'),
            'encryption' => $settings['email.smtp_encryption'] ?? config('mail.mailers.smtp.encryption'),
            'from_address' => $settings['email.from_address'] ?? config('mail.from.address'),
            'from_name' => $settings['email.from_name'] ?? config('mail.from.name'),
        ];
    }

    /**
     * Temporarily update mail configuration
     * Temporarily update mail configuration
     *
     * @param  array<string, mixed>  $config
     */
    private function updateMailConfig(array $config): void
    {
        Config::set('mail.mailers.smtp.host', $config['host']);
        Config::set('mail.mailers.smtp.port', $config['port']);
        Config::set('mail.mailers.smtp.username', $config['username'] ?? null);
        Config::set('mail.mailers.smtp.password', $config['password'] ?? null);
        Config::set('mail.mailers.smtp.encryption', $config['encryption'] ?? null);
        Config::set('mail.from.address', $config['from_address']);
        Config::set('mail.from.name', $config['from_name'] ?? config('app.name'));
    }

    /**
     * Log email sent event
     * Log email sent event
     *
     * @param  array<string, mixed>  $data
     */
    private function logEmailSent(array $data): void
    {
        $logsRaw = Cache::get('email_logs', []);
        $logs = is_array($logsRaw) ? $logsRaw : [];
        $logs[] = array_merge($data, [
            'sent_at' => now()->toIso8601String(),
        ]);

        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }

        Cache::put('email_logs', $logs, now()->addDays(7));
    }
}
