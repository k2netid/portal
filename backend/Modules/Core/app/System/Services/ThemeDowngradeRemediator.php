<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Setting;

/**
 * Downgrade remediator for theme entitlement violations.
 *
 * Called by LicenseService after a tier change (syncHeartbeat, deactivateLicense, license:check).
 * ADR-023 §2.7: if served theme is outside entitlement, revert served to janari and clear snapshot.
 */
class ThemeDowngradeRemediator
{
    /**
     * @return array{
     *   remediated: bool,
     *   previous_slug: string|null,
     *   new_slug: string|null,
     *   message: string
     * }
     */
    public function remediate(): array
    {
        // Bail early if Layout module is not loaded
        if (! class_exists(\Modules\Layout\Models\Theme::class)) {
            return [
                'remediated' => false,
                'previous_slug' => null,
                'new_slug' => null,
                'message' => 'Layout module not available; skipping theme remediation.',
            ];
        }

        try {
            /** @var LicenseService $license */
            $license = app(LicenseService::class);

            // Find the currently served (is_active) frontend theme
            $servedTheme = \Modules\Layout\Models\Theme::query()
                ->where('type', 'frontend')
                ->where('is_active', true)
                ->first();

            if (! $servedTheme) {
                return [
                    'remediated' => false,
                    'previous_slug' => null,
                    'new_slug' => null,
                    'message' => 'No active frontend theme found; nothing to remediate.',
                ];
            }

            $servedSlug = (string) $servedTheme->slug;

            // Check entitlement
            if ($license->isThemeServeEntitled($servedSlug)) {
                return [
                    'remediated' => false,
                    'previous_slug' => $servedSlug,
                    'new_slug' => null,
                    'message' => "Served theme [{$servedSlug}] is still entitled; no remediation needed.",
                ];
            }

            // Not entitled: force revert to janari
            Log::warning('[ThemeDowngradeRemediator] Served theme not entitled after tier change; reverting to janari.', [
                'previous_slug' => $servedSlug,
                'tier' => $license->getLicenseTier(),
            ]);

            // Deactivate all frontend themes
            \Modules\Layout\Models\Theme::query()
                ->where('type', 'frontend')
                ->update(['is_active' => false]);

            // Activate janari
            $janari = \Modules\Layout\Models\Theme::query()
                ->where('slug', 'janari')
                ->first();

            if ($janari) {
                $janari->update(['is_active' => true, 'status' => 'active']);
                Setting::set('theme_active', 'janari', 'string', 'layout');
            }

            // Clear frontend_theme_snapshot_v1 (ADR-023 §2.7)
            Setting::set('frontend_theme_snapshot_v1', null);

            // Clear theme caches
            if (class_exists(\Modules\Layout\Services\ThemeCacheService::class)) {
                try {
                    app(\Modules\Layout\Services\ThemeCacheService::class)->clearAll();
                } catch (\Throwable) {
                    // non-fatal
                }
            }

            Log::info('[ThemeDowngradeRemediator] Reverted served theme to janari.', [
                'previous_slug' => $servedSlug,
            ]);

            return [
                'remediated' => true,
                'previous_slug' => $servedSlug,
                'new_slug' => 'janari',
                'message' => "Served theme reverted from [{$servedSlug}] to [janari] due to license downgrade.",
            ];
        } catch (\Throwable $e) {
            Log::error('[ThemeDowngradeRemediator] Remediation failed: '.$e->getMessage());

            return [
                'remediated' => false,
                'previous_slug' => null,
                'new_slug' => null,
                'message' => 'Remediation error: '.$e->getMessage(),
            ];
        }
    }
}
