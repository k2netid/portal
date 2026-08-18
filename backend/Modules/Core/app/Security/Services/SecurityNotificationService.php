<?php

declare(strict_types=1);

namespace Modules\Core\Security\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Core\System\Models\Setting;

/**
 * Security Notification Service.
 * Sends real-time security alerts via:
 * - Telegram Bot API
 * - Email
 * - Webhook (arbitrary URL)
 * Includes deduplication to prevent notification spam.
 */
class SecurityNotificationService
{
    /** @var int Minimum seconds between same-type notifications */
    private const DEDUP_WINDOW_SECONDS = 300; // 5 minutes

    /**
     * Send a test notification to all configured channels.
     */
    public function sendTestNotification(): void
    {
        $this->send(
            'test_notification',
            'Test Security Alert',
            'This is a test security notification from your dashboard. If you received this, your notification channels are correctly configured.',
            self::SEVERITY_INFO,
            ['test' => true, 'timestamp' => now()->toIso8601String()]
        );
    }

    /**
     * Alert severity levels.
     */
    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Send a security alert to all configured channels.
     *
     * @param  string  $type  Event type (e.g. 'waf_violation', 'brute_force', 'auto_block')
     * @param  string  $title  Short title for the alert
     * @param  string  $message  Detailed message
     * @param  string  $severity  One of: info, warning, critical
     * @param  array<string, mixed>  $metadata  Additional context (IP, path, etc.)
     */
    public function send(
        string $type,
        string $title,
        string $message,
        string $severity = self::SEVERITY_WARNING,
        array $metadata = [],
    ): void {
        // Deduplicate: skip if same type sent within the window
        if ($this->isDuplicate($type, $metadata)) {
            return;
        }

        $this->markSent($type, $metadata);

        $config = $this->getConfig();

        // Telegram
        if (! empty($config['telegram_bot_token']) && (isset($config['telegram_chat_id']) && ($config['telegram_chat_id'] !== '' && $config['telegram_chat_id'] !== '0'))) {
            $this->sendTelegram($config['telegram_bot_token'], $config['telegram_chat_id'], $title, $message, $severity, $metadata);
        }

        // Email
        if (! empty($config['email_to'])) {
            $this->sendEmail($config['email_to'], $title, $message, $severity, $metadata);
        }

        // Webhook
        if (! empty($config['webhook_url'])) {
            $this->sendWebhook($config['webhook_url'], $type, $title, $message, $severity, $metadata);
        }
    }

    /**
     * Send a critical alert (convenience method).
     *
     * @param  array<string, mixed>  $metadata
     */
    public function critical(string $type, string $title, string $message, array $metadata = []): void
    {
        $this->send($type, $title, $message, self::SEVERITY_CRITICAL, $metadata);
    }

    /**
     * Send a warning alert (convenience method).
     *
     * @param  array<string, mixed>  $metadata
     */
    public function warning(string $type, string $title, string $message, array $metadata = []): void
    {
        $this->send($type, $title, $message, self::SEVERITY_WARNING, $metadata);
    }

    /**
     * Send a Telegram message via Bot API.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function sendTelegram(
        string $botToken,
        string $chatId,
        string $title,
        string $message,
        string $severity,
        array $metadata,
    ): void {
        $emoji = match ($severity) {
            self::SEVERITY_CRITICAL => '🚨',
            self::SEVERITY_WARNING => '⚠️',
            default => 'ℹ️',
        };

        $text = "{$emoji} *{$title}*\n\n{$message}";

        if ($metadata !== []) {
            $text .= "\n\n*Details:*";
            foreach ($metadata as $key => $value) {
                $val = is_array($value) ? (string) json_encode($value) : (is_scalar($value) ? (string) $value : '');
                $text .= "\n• `{$key}`: {$val}";
            }
        }

        $text .= "\n\n🕐 ".now()->format('Y-m-d H:i:s T');

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Exception $e) {
            Log::channel('security')->error('Telegram notification failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send an email notification.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function sendEmail(
        string $to,
        string $title,
        string $message,
        string $severity,
        array $metadata,
    ): void {
        try {
            $severityLabel = strtoupper($severity);

            $body = "Security Alert [{$severityLabel}]\n\n";
            $body .= "{$title}\n\n{$message}\n";

            if ($metadata !== []) {
                $body .= "\nDetails:\n";
                foreach ($metadata as $key => $value) {
                    $val = is_array($value) ? (string) json_encode($value) : (is_scalar($value) ? (string) $value : '');
                    $body .= "  - {$key}: {$val}\n";
                }
            }

            $body .= "\nTimestamp: ".now()->format('Y-m-d H:i:s T');
            $body .= "\nServer: ".gethostname();

            $appName = is_string($appNameRaw = config('app.name')) ? $appNameRaw : 'K2NET';

            Mail::raw($body, function ($mail) use ($to, $title, $severityLabel, $appName): void {
                $mail->to($to)
                    ->subject("[{$appName}] [{$severityLabel}] {$title}");
            });
        } catch (\Exception $e) {
            Log::channel('security')->error('Email notification failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a webhook POST request.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function sendWebhook(
        string $url,
        string $type,
        string $title,
        string $message,
        string $severity,
        array $metadata,
    ): void {
        try {
            Http::timeout(5)->post($url, [
                'event' => 'security_alert',
                'type' => $type,
                'severity' => $severity,
                'title' => $title,
                'message' => $message,
                'metadata' => $metadata,
                'timestamp' => now()->toIso8601String(),
                'server' => gethostname(),
            ]);
        } catch (\Exception $e) {
            Log::channel('security')->error('Webhook notification failed', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
        }
    }

    /**
     * Check if this notification was already sent recently.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function isDuplicate(string $type, array $metadata): bool
    {
        $key = $this->getDedupKey($type, $metadata);

        return Cache::has($key);
    }

    /**
     * Mark a notification as sent for deduplication.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function markSent(string $type, array $metadata): void
    {
        $key = $this->getDedupKey($type, $metadata);
        Cache::put($key, true, self::DEDUP_WINDOW_SECONDS);
    }

    /**
     * Generate deduplication cache key.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function getDedupKey(string $type, array $metadata): string
    {
        $ip = 'unknown';
        if (isset($metadata['ip']) && is_string($metadata['ip'])) {
            $ip = $metadata['ip'];
        } elseif (isset($metadata['ip_address']) && is_string($metadata['ip_address'])) {
            $ip = $metadata['ip_address'];
        }

        return "security_notif_dedup:{$type}:{$ip}";
    }

    /**
     * Get notification configuration from settings.
     *
     * @return array{telegram_bot_token: string|null, telegram_chat_id: string|null, email_to: string|null, webhook_url: string|null}
     */
    private function getConfig(): array
    {
        return Cache::remember('security_notification_config', 300, function (): array {
            try {
                /** @var array<string, string|null> $settings */
                $settings = Setting::where('group', 'security')
                    ->whereIn('key', [
                        'telegram_bot_token',
                        'telegram_chat_id',
                        'email_to',
                        'webhook_url',
                    ])
                    ->pluck('value', 'key')
                    ->toArray();

                return [
                    'telegram_bot_token' => $settings['telegram_bot_token'] ?? null,
                    'telegram_chat_id' => $settings['telegram_chat_id'] ?? null,
                    'email_to' => $settings['email_to'] ?? null,
                    'webhook_url' => $settings['webhook_url'] ?? null,
                ];
            } catch (\Exception) {
                return [
                    'telegram_bot_token' => null,
                    'telegram_chat_id' => null,
                    'email_to' => null,
                    'webhook_url' => null,
                ];
            }
        });
    }
}
