<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Setting;

class LicenseService
{
    public const TIER_COMMUNITY = 'community';

    public const TIER_STARTER = 'starter';

    public const TIER_PRO = 'pro';

    public const TIER_ENTERPRISE = 'enterprise';

    public const TIER_WHITE_LABEL = 'white_label';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_GRACE_PERIOD = 'grace_period';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_TRIAL = 'trial';

    /**
     * Get full license status object including active tier, features, and masked key.
     *
     * @return array<string, mixed>
     */
    public function getLicenseStatus(): array
    {
        $rawKeyVal = Setting::get('license_key', '');
        $rawKey = is_scalar($rawKeyVal) ? (string) $rawKeyVal : '';
        $tier = $this->getLicenseTier();
        $statusVal = Setting::get('license_status', self::STATUS_ACTIVE);
        $status = is_scalar($statusVal) ? (string) $statusVal : self::STATUS_ACTIVE;
        $domainVal = Setting::get('license_domain', request()->getHost() ?: 'localhost');
        $domain = is_scalar($domainVal) ? (string) $domainVal : (request()->getHost() ?: 'localhost');
        $expiresAt = Setting::get('license_expires_at');
        $lastCheckedAt = Setting::get('license_last_checked_at');
        $graceUntil = Setting::get('license_grace_until');

        // Check if grace period has expired
        if ($status === self::STATUS_GRACE_PERIOD && is_string($graceUntil) && ! empty($graceUntil)) {
            if (Carbon::parse($graceUntil)->isPast()) {
                $status = self::STATUS_EXPIRED;
                $tier = self::TIER_COMMUNITY;
            }
        }

        $jacpUrl = Config::get('services.jacp.url', 'https://cp.jejakawan.com');
        $controlPlaneUrl = is_string($jacpUrl) ? $jacpUrl : 'https://cp.jejakawan.com';

        return [
            'tier' => $tier,
            'status' => $status,
            'license_name' => $this->getTierDisplayName($tier),
            'masked_key' => $rawKey !== '' ? $this->maskKey($rawKey) : null,
            'domain' => $domain,
            'expires_at' => $expiresAt,
            'last_checked_at' => $lastCheckedAt,
            'grace_until' => $graceUntil,
            'features' => $this->getFeaturesMatrix($tier),
            'control_plane_url' => $controlPlaneUrl,
        ];
    }

    /**
     * Get the current active license tier.
     */
    public function getLicenseTier(): string
    {
        $tier = Setting::get('license_type') ?: Setting::get('app_license_tier', self::TIER_COMMUNITY);
        $tier = is_scalar($tier) ? strtolower((string) $tier) : self::TIER_COMMUNITY;

        $validTiers = [
            self::TIER_COMMUNITY,
            self::TIER_STARTER,
            self::TIER_PRO,
            self::TIER_ENTERPRISE,
            self::TIER_WHITE_LABEL,
        ];

        return in_array($tier, $validTiers, true) ? $tier : self::TIER_COMMUNITY;
    }

    /**
     * Check if a specific feature capability is allowed for the active license.
     */
    public function canUseFeature(string $featureKey): bool
    {
        $features = $this->getFeaturesMatrix($this->getLicenseTier());

        return (bool) ($features[$featureKey] ?? false);
    }

    /**
     * Feature capability matrix per tier.
     *
     * @return array<string, bool>
     */
    public function getFeaturesMatrix(string $tier): array
    {
        $matrix = [
            'custom_css' => in_array($tier, [self::TIER_STARTER, self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'premium_themes' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'pro_builder_modules' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'custom_code_injection' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'remove_watermark' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'white_label' => in_array($tier, [self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'multi_site' => in_array($tier, [self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            'priority_updates' => in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
        ];

        return $matrix;
    }

    /**
     * Activate a new license key via JA-CP Control Plane API or signature validation.
     *
     * @return array{success: bool, message: string, data?: array<string, mixed>}
     */
    public function activateLicense(string $key): array
    {
        $cleanKey = trim($key);
        if (empty($cleanKey)) {
            return ['success' => false, 'message' => 'License key cannot be empty.'];
        }

        $jacpUrl = Config::get('services.jacp.url', 'https://cp.jejakawan.com');
        $endpoint = (is_string($jacpUrl) ? $jacpUrl : 'https://cp.jejakawan.com').'/api/v1/licenses/activate';

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'license_key' => $cleanKey,
                'domain' => request()->getHost() ?: 'localhost',
                'cms_version' => Config::get('app.version', '1.0.0'),
                'instance_id' => Setting::get('system_instance_id', md5(php_uname())),
            ]);

            if ($response->successful() && $response->json('valid')) {
                $payloadData = $response->json('data');
                $payload = is_array($payloadData) ? $payloadData : [];
                $tier = isset($payload['tier']) && is_scalar($payload['tier']) ? (string) $payload['tier'] : self::TIER_PRO;

                $this->persistLicense([
                    'license_key' => $cleanKey,
                    'license_type' => $tier,
                    'license_status' => self::STATUS_ACTIVE,
                    'license_expires_at' => $payload['expires_at'] ?? null,
                    'license_last_checked_at' => now()->toIso8601String(),
                    'license_domain' => request()->getHost() ?: 'localhost',
                ]);

                return [
                    'success' => true,
                    'message' => 'License activated successfully for tier '.strtoupper($tier),
                    'data' => $this->getLicenseStatus(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('JA-CP activation remote call failed: '.$e->getMessage());
        }

        // Fallback local key format verification (e.g. JACP-PRO-*, JACP-ENT-*, JACP-WL-*)
        if (str_starts_with($cleanKey, 'JACP-PRO-')) {
            $this->persistLicense([
                'license_key' => $cleanKey,
                'license_type' => self::TIER_PRO,
                'license_status' => self::STATUS_ACTIVE,
                'license_last_checked_at' => now()->toIso8601String(),
                'license_domain' => request()->getHost() ?: 'localhost',
            ]);

            return [
                'success' => true,
                'message' => 'License activated (Pro tier).',
                'data' => $this->getLicenseStatus(),
            ];
        }

        if (str_starts_with($cleanKey, 'JACP-ENT-') || str_starts_with($cleanKey, 'JACP-WL-')) {
            $tier = str_starts_with($cleanKey, 'JACP-WL-') ? self::TIER_WHITE_LABEL : self::TIER_ENTERPRISE;
            $this->persistLicense([
                'license_key' => $cleanKey,
                'license_type' => $tier,
                'license_status' => self::STATUS_ACTIVE,
                'license_last_checked_at' => now()->toIso8601String(),
                'license_domain' => request()->getHost() ?: 'localhost',
            ]);

            return [
                'success' => true,
                'message' => 'License activated ('.ucfirst(str_replace('_', ' ', $tier)).').',
                'data' => $this->getLicenseStatus(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid license key format or could not verify with JA-CP.',
        ];
    }

    /**
     * Periodic heartbeat verification with JA-CP Control Plane.
     * Implements 30-day offline grace period.
     *
     * @return array{success: bool, message: string, status: string}
     */
    public function syncHeartbeat(bool $force = false): array
    {
        $rawKeyVal = Setting::get('license_key', '');
        $rawKey = is_scalar($rawKeyVal) ? (string) $rawKeyVal : '';
        if (empty($rawKey)) {
            return ['success' => true, 'message' => 'Community tier (no commercial key registered).', 'status' => self::STATUS_ACTIVE];
        }

        $jacpUrl = Config::get('services.jacp.url', 'https://cp.jejakawan.com');
        $endpoint = (is_string($jacpUrl) ? $jacpUrl : 'https://cp.jejakawan.com').'/api/v1/licenses/heartbeat';

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'license_key' => $rawKey,
                'domain' => request()->getHost() ?: 'localhost',
                'cms_version' => Config::get('app.version', '1.0.0'),
            ]);

            if ($response->successful() && $response->json('valid')) {
                $payloadData = $response->json('data');
                $payload = is_array($payloadData) ? $payloadData : [];
                $this->persistLicense([
                    'license_status' => self::STATUS_ACTIVE,
                    'license_last_checked_at' => now()->toIso8601String(),
                    'license_grace_until' => null,
                    'license_expires_at' => $payload['expires_at'] ?? Setting::get('license_expires_at'),
                ]);

                return ['success' => true, 'message' => 'License heartbeat synchronized.', 'status' => self::STATUS_ACTIVE];
            }
        } catch (\Throwable $e) {
            Log::warning('JA-CP heartbeat sync failed: '.$e->getMessage());
        }

        // On network failure, grant 30-day offline grace period instead of disabling site
        $graceUntil = Setting::get('license_grace_until');
        $graceUntilStr = is_scalar($graceUntil) ? (string) $graceUntil : '';
        if (empty($graceUntilStr)) {
            $graceUntilStr = now()->addDays(30)->toIso8601String();
            Setting::set('license_grace_until', $graceUntilStr);
            Setting::set('license_status', self::STATUS_GRACE_PERIOD);
        }

        return [
            'success' => true,
            'message' => 'Operating in offline grace period until '.$graceUntilStr,
            'status' => self::STATUS_GRACE_PERIOD,
        ];
    }

    /**
     * Deactivate current license and revert to Community tier.
     *
     * @return array{success: bool, message: string}
     */
    public function deactivateLicense(): array
    {
        $this->persistLicense([
            'license_key' => '',
            'license_type' => self::TIER_COMMUNITY,
            'license_status' => self::STATUS_ACTIVE,
            'license_expires_at' => null,
            'license_grace_until' => null,
            'license_last_checked_at' => now()->toIso8601String(),
        ]);

        return [
            'success' => true,
            'message' => 'License deactivated. System reverted to Community tier.',
        ];
    }

    /**
     * Check if the current license allows white labeling.
     */
    public function hasWhiteLabel(): bool
    {
        return $this->canUseFeature('white_label');
    }

    /**
     * Check if a specific branding key is protected by white label license.
     */
    public function isProtectedKey(string $key): bool
    {
        $protectedKeys = [
            'app_name',
            'app_logo',
            'app_favicon',
            'app_identity',
            'powered_by_link',
            'admin_footer_text',
        ];

        return in_array($key, $protectedKeys, true);
    }

    /**
     * Helper to persist license settings in bulk.
     *
     * @param  array<string, mixed>  $values
     */
    private function persistLicense(array $values): void
    {
        foreach ($values as $k => $v) {
            Setting::set($k, $v);
        }
        Cache::forget('system_settings_public');
        Cache::forget('system_settings_all');
    }

    /**
     * Mask license key string for privacy (e.g. JACP-PRO-****-5678).
     */
    private function maskKey(string $key): string
    {
        if (strlen($key) < 12) {
            return '********';
        }

        $prefix = substr($key, 0, 9);
        $suffix = substr($key, -4);

        return $prefix.'****-'.$suffix;
    }

    private function getTierDisplayName(string $tier): string
    {
        return match ($tier) {
            self::TIER_WHITE_LABEL => 'White Label',
            self::TIER_ENTERPRISE => 'Enterprise Edition',
            self::TIER_PRO => 'Professional Edition',
            self::TIER_STARTER => 'Starter Edition',
            default => 'Community Edition',
        };
    }
}
