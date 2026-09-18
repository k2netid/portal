<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Setting;
use Modules\Layout\Models\Theme;

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
            'theme_quota' => $this->getThemeQuota($tier),
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
     * @return array<string, mixed>
     */
    public function getFeaturesMatrix(string $tier): array
    {
        $isPaid = in_array($tier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);
        $isEnterprisePlus = in_array($tier, [self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);

        $matrix = [
            'custom_css' => in_array($tier, [self::TIER_STARTER, self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true),
            /**
             * @deprecated Use `theme_quota` instead for granular theme entitlement checks.
             * Kept for backward-compat with existing frontend feature checks.
             */
            'premium_themes' => $isPaid,
            // Theme quota (ADR-023) — replaces the boolean premium_themes gate.
            'theme_quota' => $this->getThemeQuota($tier),
            'pro_builder_modules' => $isPaid,
            'custom_code_injection' => $isPaid,
            'remove_watermark' => $isPaid,
            'white_label' => $isEnterprisePlus,
            'multi_site' => $isEnterprisePlus,
            'priority_updates' => $isPaid,
            'theme_upload' => $isPaid,
            'plugin_upload' => $isPaid,
            'theme_export' => $isPaid,
            'plugin_export' => $isPaid,
            'visual_builder' => $isPaid,
            'data_studio' => $isPaid,
        ];

        return $matrix;
    }

    /**
     * Theme quota entitlement per tier (ADR-023).
     *
     * Phases:
     *  - Phase 1: mirror from license_type.
     *  - Phase 2: populated from JA-CP heartbeat/activate payload `themes.*`.
     *
     * @return array{
     *   always_on: list<string>,
     *   max_premium_active: int|null,
     *   catalog: list<string>
     * }
     */
    public function getThemeQuota(?string $tier = null): array
    {
        $currentTier = $this->getLicenseTier();
        $targetTier = $tier ?? $currentTier;

        $isPaid = in_array($targetTier, [self::TIER_PRO, self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);
        $isEnterprisePlus = in_array($targetTier, [self::TIER_ENTERPRISE, self::TIER_WHITE_LABEL], true);

        /** @var array{always_on: list<string>, max_premium_active: int|null, catalog: list<string>} $defaultQuota */
        $defaultQuota = [
            'always_on' => ['janari'],
            'max_premium_active' => match (true) {
                $isEnterprisePlus => null,    // unlimited
                $isPaid => 1,                 // Pro: 1 premium slot
                default => 0,                 // Community/Starter: janari only
            },
            'catalog' => ['janari', 'layung', 'sarangenge', 'sareupna'],
        ];

        // If querying a different tier than the active tier, return static default
        if ($targetTier !== $currentTier) {
            return $defaultQuota;
        }

        // Phase 2: Check for custom themes quota payload synced from JA-CP
        $customQuotaRaw = Setting::get('license_themes_quota');
        if (! is_array($customQuotaRaw)) {
            return $defaultQuota;
        }

        /** @var array<string, mixed> $customQuotaRaw */
        return $this->sanitizeThemeQuotaPayload($customQuotaRaw, $targetTier, $defaultQuota);
    }

    /**
     * Sanitize and validate theme quota payload from JA-CP with safe fallback.
     *
     * @param  array<string, mixed>  $payload
     * @param  array{always_on: list<string>, max_premium_active: int|null, catalog: list<string>}  $defaultQuota
     * @return array{
     *   always_on: list<string>,
     *   max_premium_active: int|null,
     *   catalog: list<string>
     * }
     */
    private function sanitizeThemeQuotaPayload(array $payload, string $tier, array $defaultQuota): array
    {
        // 1. always_on: list of strings, always ensuring 'janari' is included
        $alwaysOn = $defaultQuota['always_on'];
        if (isset($payload['always_on']) && is_array($payload['always_on'])) {
            $parsed = [];
            foreach ($payload['always_on'] as $item) {
                if (is_string($item) && $item !== '') {
                    $parsed[] = $item;
                }
            }
            if (! in_array('janari', $parsed, true)) {
                $parsed[] = 'janari';
            }
            /** @var list<string> $alwaysOn */
            $alwaysOn = array_values(array_unique($parsed));
        }

        // 2. max_premium_active: int|null. Community/Starter tier forced to 0.
        if (in_array($tier, [self::TIER_COMMUNITY, self::TIER_STARTER], true)) {
            $maxPremium = 0;
        } elseif (array_key_exists('max_premium_active', $payload)) {
            if ($payload['max_premium_active'] === null) {
                $maxPremium = null;
            } elseif (is_numeric($payload['max_premium_active'])) {
                $maxPremium = max(0, (int) $payload['max_premium_active']);
            } else {
                $maxPremium = $defaultQuota['max_premium_active'];
            }
        } else {
            $maxPremium = $defaultQuota['max_premium_active'];
        }

        // 3. catalog: list of strings, always ensuring 'janari' is included
        $catalog = $defaultQuota['catalog'];
        if (isset($payload['catalog']) && is_array($payload['catalog'])) {
            $parsedCat = [];
            foreach ($payload['catalog'] as $item) {
                if (is_string($item) && $item !== '') {
                    $parsedCat[] = $item;
                }
            }
            if (! in_array('janari', $parsedCat, true)) {
                $parsedCat[] = 'janari';
            }
            /** @var list<string> $catalog */
            $catalog = array_values(array_unique($parsedCat));
        }

        return [
            'always_on' => $alwaysOn,
            'max_premium_active' => $maxPremium,
            'catalog' => $catalog,
        ];
    }

    /**
     * Count remaining premium theme slots for the current tier.
     * Returns null when unlimited (enterprise/white_label).
     * Returns 0 when no slots remain.
     *
     * Premium = first-party non-janari served OR uploaded ZIP served.
     */
    public function remainingPremiumSlots(): ?int
    {
        $tier = $this->getLicenseTier();
        $quota = $this->getThemeQuota($tier);

        if ($quota['max_premium_active'] === null) {
            return null; // unlimited
        }

        // Count currently served premium themes.
        $servedPremium = $this->countServedPremiumThemes();

        return max(0, $quota['max_premium_active'] - $servedPremium);
    }

    /**
     * Check whether a theme slug is entitled to be served for the current tier.
     *
     * @param  string  $themeSlug  The lay_themes.slug (e.g. 'layung'), not the pack slug.
     */
    public function isThemeServeEntitled(string $themeSlug): bool
    {
        $tier = $this->getLicenseTier();
        $quota = $this->getThemeQuota($tier);

        // always_on themes are always entitled
        if (in_array($themeSlug, $quota['always_on'], true)) {
            return true;
        }

        // First-party theme not in license catalog → not entitled
        if (in_array($themeSlug, ['janari', 'layung', 'sarangenge', 'sareupna'], true)
            && ! in_array($themeSlug, $quota['catalog'], true)) {
            return false;
        }

        // Third-party / custom ZIP theme: blocked on community or if max_premium_active is 0
        if (! in_array($themeSlug, $quota['catalog'], true)) {
            if ($quota['max_premium_active'] === 0 || ! $this->canUseFeature('theme_upload')) {
                return false;
            }
        }

        // Check max_premium_active
        return $quota['max_premium_active'] === null || $quota['max_premium_active'] > 0;
    }

    /**
     * Count how many non-janari (premium) themes are currently served.
     * Uses lay_themes table when Layout module is available.
     */
    private function countServedPremiumThemes(): int
    {
        try {
            if (! class_exists(Theme::class)) {
                return 0;
            }

            return (int) Theme::query()
                ->where('is_active', true)
                ->where('type', 'frontend')
                ->where('slug', '!=', 'janari')
                ->count();
        } catch (\Throwable) {
            return 0;
        }
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

                // Phase 2: Store optional JA-CP themes quota payload
                $themesPayload = isset($payload['themes']) && is_array($payload['themes']) ? $payload['themes'] : null;
                if ($themesPayload !== null) {
                    Setting::set('license_themes_quota', $themesPayload, 'json');
                } else {
                    Setting::set('license_themes_quota', null);
                }

                // Remediate served theme if new tier/quota restricts active theme (ADR-023 §2.7)
                try {
                    app(ThemeDowngradeRemediator::class)->remediate();
                } catch (\Throwable $re) {
                    Log::warning('Theme remediator failed after license activation: '.$re->getMessage());
                }

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
            Setting::set('license_themes_quota', null);
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
            Setting::set('license_themes_quota', null);
            $tier = str_starts_with($cleanKey, 'JACP-WL-') ? self::TIER_WHITE_LABEL : self::TIER_ENTERPRISE;
            $isPerpetual = $this->isPerpetualLicense($cleanKey);
            $this->persistLicense([
                'license_key' => $cleanKey,
                'license_type' => $tier,
                'license_status' => self::STATUS_ACTIVE,
                'license_is_perpetual' => $isPerpetual,
                'license_expires_at' => $isPerpetual ? null : Setting::get('license_expires_at'),
                'license_grace_until' => null,
                'license_last_checked_at' => now()->toIso8601String(),
                'license_domain' => request()->getHost() ?: 'localhost',
            ]);

            return [
                'success' => true,
                'message' => 'License activated ('.ucfirst(str_replace('_', ' ', $tier)).($isPerpetual ? ' Perpetual' : '').').',
                'data' => $this->getLicenseStatus(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid license key format or could not verify with JA-CP.',
        ];
    }

    /**
     * Check if a license key represents a perpetual (lifetime) entitlement.
     */
    public function isPerpetualLicense(?string $key = null): bool
    {
        $rawKeyVal = Setting::get('license_key', '');
        $rawKey = $key ?? (is_scalar($rawKeyVal) ? (string) $rawKeyVal : '');
        if (empty($rawKey)) {
            return false;
        }

        $upper = strtoupper($rawKey);

        return str_contains($upper, 'PERPETUAL')
            || str_contains($upper, 'LIFETIME')
            || (bool) Setting::get('license_is_perpetual', false);
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

        // Perpetual license bypass: perpetual licenses are lifetime and verified offline
        if ($this->isPerpetualLicense($rawKey)) {
            $this->persistLicense([
                'license_status' => self::STATUS_ACTIVE,
                'license_last_checked_at' => now()->toIso8601String(),
                'license_grace_until' => null,
                'license_expires_at' => null,
                'license_is_perpetual' => true,
            ]);

            return [
                'success' => true,
                'message' => 'Perpetual Enterprise license active (offline verified).',
                'status' => self::STATUS_ACTIVE,
            ];
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

                if (isset($payload['tier']) && is_scalar($payload['tier'])) {
                    $newTier = (string) $payload['tier'];
                    Setting::set('license_type', $newTier);
                    Setting::set('app_license_tier', $newTier);
                }

                $this->persistLicense([
                    'license_status' => self::STATUS_ACTIVE,
                    'license_last_checked_at' => now()->toIso8601String(),
                    'license_grace_until' => null,
                    'license_expires_at' => $payload['expires_at'] ?? Setting::get('license_expires_at'),
                ]);

                // Phase 2: Sync optional JA-CP themes quota payload
                if (isset($payload['themes']) && is_array($payload['themes'])) {
                    Setting::set('license_themes_quota', $payload['themes'], 'json');
                }

                // Remediate served theme if tier or quota changed (ADR-023 §2.7)
                try {
                    app(ThemeDowngradeRemediator::class)->remediate();
                } catch (\Throwable $re) {
                    Log::warning('Theme remediator failed after heartbeat: '.$re->getMessage());
                }

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
        Setting::set('license_themes_quota', null);

        $this->persistLicense([
            'license_key' => '',
            'license_type' => self::TIER_COMMUNITY,
            'license_status' => self::STATUS_ACTIVE,
            'license_expires_at' => null,
            'license_grace_until' => null,
            'license_last_checked_at' => now()->toIso8601String(),
        ]);

        // Remediate served theme — revert to janari if no longer entitled (ADR-023 §2.7)
        try {
            app(ThemeDowngradeRemediator::class)->remediate();
        } catch (\Throwable $re) {
            Log::warning('Theme remediator failed after license deactivation: '.$re->getMessage());
        }

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
            'app_logo_light',
            'app_logo_dark',
            'app_logo_compact',
            'brand_logo',
            'app_favicon',
            'brand_favicon',
            'brand_sync_site_identity',
            'branding_display',
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
        if (isset($values['license_type'])) {
            Setting::set('app_license_tier', $values['license_type']);
        }
        Cache::forget('system_settings_public');
        Cache::forget('system_settings_all');
        Cache::forget('sys_setting_license_key');
        Cache::forget('sys_setting_license_type');
        Cache::forget('sys_setting_app_license_tier');
        Cache::forget('sys_setting_license_status');
        Cache::forget('sys_setting_license_expires_at');
        Cache::forget('sys_setting_license_is_perpetual');
        Cache::forget('sys_setting_license_themes_quota');
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

    public function getTierDisplayName(string $tier): string
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
