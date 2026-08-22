<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Config;
use Modules\Core\System\Models\Setting;

/**
 * Apply global email Settings (group=email, keys mail_*) to Laravel mail config.
 */
class SystemMailConfig
{
    /**
     * @return array{
     *     host: mixed,
     *     port: mixed,
     *     username: mixed,
     *     password: mixed,
     *     encryption: mixed,
     *     from_address: mixed,
     *     from_name: mixed
     * }
     */
    public function resolve(): array
    {
        $settings = Setting::getGroup('email');

        return [
            'host' => $this->stringSetting($settings, 'mail_host')
                ?? $this->stringSetting($settings, 'email.smtp_host')
                ?? config('mail.mailers.smtp.host'),
            'port' => $this->intSetting($settings, 'mail_port')
                ?? $this->intSetting($settings, 'email.smtp_port')
                ?? config('mail.mailers.smtp.port'),
            'username' => $this->stringSetting($settings, 'mail_username')
                ?? $this->stringSetting($settings, 'email.smtp_username')
                ?? config('mail.mailers.smtp.username'),
            'password' => $this->stringSetting($settings, 'mail_password')
                ?? $this->stringSetting($settings, 'email.smtp_password')
                ?? config('mail.mailers.smtp.password'),
            'encryption' => $this->normalizeEncryption(
                $this->stringSetting($settings, 'mail_encryption')
                    ?? $this->stringSetting($settings, 'email.smtp_encryption')
                    ?? config('mail.mailers.smtp.encryption'),
            ),
            'from_address' => $this->stringSetting($settings, 'mail_from_address')
                ?? $this->stringSetting($settings, 'email.from_address')
                ?? config('mail.from.address'),
            'from_name' => $this->stringSetting($settings, 'mail_from_name')
                ?? $this->stringSetting($settings, 'email.from_name')
                ?? config('mail.from.name'),
        ];
    }

    /**
     * @return array{
     *     host: mixed,
     *     port: mixed,
     *     username: mixed,
     *     password: mixed,
     *     encryption: mixed,
     *     from_address: mixed,
     *     from_name: mixed
     * }
     */
    public function apply(): array
    {
        $config = $this->resolve();

        Config::set('mail.mailers.smtp.host', $config['host']);
        Config::set('mail.mailers.smtp.port', $config['port']);
        Config::set('mail.mailers.smtp.username', $config['username']);
        Config::set('mail.mailers.smtp.password', $config['password']);
        Config::set('mail.mailers.smtp.encryption', $config['encryption']);

        if (is_string($config['from_address']) && $config['from_address'] !== '') {
            Config::set('mail.from.address', $config['from_address']);
        }
        if (is_string($config['from_name']) && $config['from_name'] !== '') {
            Config::set('mail.from.name', $config['from_name']);
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function stringSetting(array $settings, string $key): ?string
    {
        $value = $settings[$key] ?? null;
        if (! is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function intSetting(array $settings, string $key): ?int
    {
        $value = $settings[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function normalizeEncryption(mixed $encryption): ?string
    {
        if (! is_scalar($encryption) || $encryption === '') {
            return null;
        }

        $value = strtolower(trim((string) $encryption));
        if ($value === '' || $value === 'null' || $value === 'none') {
            return null;
        }

        return $value;
    }
}
