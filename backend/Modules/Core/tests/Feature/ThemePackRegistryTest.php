<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Core\System\Services\LicenseService;
use Modules\Core\System\Support\ExtensionFamilyCatalog;
use Tests\TestCase;

/**
 * Feature tests for ADR-023 Phase 1: Theme Registry Packs + License Quotas.
 *
 * Tests cover:
 *  - ExtensionFamilyCatalog THEME family helpers
 *  - LicenseService::getThemeQuota() per tier
 *  - LicenseService::isThemeServeEntitled() per tier
 *  - LicenseService::remainingPremiumSlots()
 */
final class ThemePackRegistryTest extends TestCase
{
    private LicenseService $license;

    protected function setUp(): void
    {
        parent::setUp();
        $this->license = app(LicenseService::class);
    }

    // -----------------------------------------------------------------------
    // ExtensionFamilyCatalog helpers
    // -----------------------------------------------------------------------

    public function test_theme_family_constant_exists(): void
    {
        $this->assertSame('theme', ExtensionFamilyCatalog::THEME);
    }

    public function test_first_party_theme_pack_slugs(): void
    {
        $slugs = ExtensionFamilyCatalog::firstPartyThemePackSlugs();

        $this->assertContains('theme-janari', $slugs);
        $this->assertContains('theme-layung', $slugs);
        $this->assertContains('theme-sarangenge', $slugs);
        $this->assertContains('theme-sareupna', $slugs);
        $this->assertCount(4, $slugs);
    }

    public function test_premium_theme_pack_slugs_exclude_janari(): void
    {
        $premiumSlugs = ExtensionFamilyCatalog::premiumThemePackSlugs();

        $this->assertNotContains('theme-janari', $premiumSlugs);
        $this->assertContains('theme-layung', $premiumSlugs);
        $this->assertContains('theme-sarangenge', $premiumSlugs);
        $this->assertContains('theme-sareupna', $premiumSlugs);
        $this->assertCount(3, $premiumSlugs);
    }

    public function test_resolve_type_theme_maps_to_theme_family(): void
    {
        $family = ExtensionFamilyCatalog::resolve(null, 'theme-custom', 'theme', false);
        $this->assertSame(ExtensionFamilyCatalog::THEME, $family);
    }

    public function test_theme_pack_activation_priority_after_layout(): void
    {
        $layoutPriority = ExtensionFamilyCatalog::activationPriority('layout');
        $janariPriority = ExtensionFamilyCatalog::activationPriority('theme-janari');

        $this->assertGreaterThan($layoutPriority, $janariPriority);
    }

    // -----------------------------------------------------------------------
    // LicenseService::getThemeQuota()
    // -----------------------------------------------------------------------

    public function test_community_quota_janari_only(): void
    {
        $quota = $this->license->getThemeQuota(LicenseService::TIER_COMMUNITY);

        $this->assertSame(['janari'], $quota['always_on']);
        $this->assertSame(0, $quota['max_premium_active']);
        $this->assertContains('janari', $quota['catalog']);
    }

    public function test_starter_quota_same_as_community(): void
    {
        $quota = $this->license->getThemeQuota(LicenseService::TIER_STARTER);

        $this->assertSame(0, $quota['max_premium_active']);
    }

    public function test_pro_quota_allows_one_premium(): void
    {
        $quota = $this->license->getThemeQuota(LicenseService::TIER_PRO);

        $this->assertSame(1, $quota['max_premium_active']);
        $this->assertSame(['janari'], $quota['always_on']);
    }

    public function test_enterprise_quota_unlimited(): void
    {
        $quota = $this->license->getThemeQuota(LicenseService::TIER_ENTERPRISE);

        $this->assertNull($quota['max_premium_active']);
    }

    public function test_white_label_quota_unlimited(): void
    {
        $quota = $this->license->getThemeQuota(LicenseService::TIER_WHITE_LABEL);

        $this->assertNull($quota['max_premium_active']);
    }

    // -----------------------------------------------------------------------
    // LicenseService::isThemeServeEntitled()
    // -----------------------------------------------------------------------

    public function test_community_can_serve_janari(): void
    {
        $this->license->deactivateLicense(); // revert to community

        $this->assertTrue($this->license->isThemeServeEntitled('janari'));
    }

    public function test_community_cannot_serve_premium_theme(): void
    {
        $this->license->deactivateLicense();

        $this->assertFalse($this->license->isThemeServeEntitled('layung'));
        $this->assertFalse($this->license->isThemeServeEntitled('sarangenge'));
        $this->assertFalse($this->license->isThemeServeEntitled('sareupna'));
    }

    public function test_pro_can_serve_janari(): void
    {
        $this->license->activateLicense('JACP-PRO-TEST-ADR023-PHASE1');

        $this->assertTrue($this->license->isThemeServeEntitled('janari'));
    }

    public function test_pro_can_serve_premium_when_slot_available(): void
    {
        $this->license->activateLicense('JACP-PRO-TEST-ADR023-PHASE1');

        // No premium currently served — slot should be available
        $this->assertTrue($this->license->isThemeServeEntitled('layung'));
    }

    public function test_enterprise_can_serve_all_themes(): void
    {
        $this->license->activateLicense('JACP-ENT-ADR023-TEST-9999');

        $this->assertTrue($this->license->isThemeServeEntitled('janari'));
        $this->assertTrue($this->license->isThemeServeEntitled('layung'));
        $this->assertTrue($this->license->isThemeServeEntitled('sarangenge'));
        $this->assertTrue($this->license->isThemeServeEntitled('sareupna'));
    }

    // -----------------------------------------------------------------------
    // LicenseService::remainingPremiumSlots()
    // -----------------------------------------------------------------------

    public function test_community_remaining_slots_is_zero(): void
    {
        $this->license->deactivateLicense();

        $remaining = $this->license->remainingPremiumSlots();
        $this->assertSame(0, $remaining);
    }

    public function test_enterprise_remaining_slots_is_null_unlimited(): void
    {
        $this->license->activateLicense('JACP-ENT-ADR023-TEST-9999');

        $remaining = $this->license->remainingPremiumSlots();
        $this->assertNull($remaining);
    }

    // -----------------------------------------------------------------------
    // features_matrix backward compat
    // -----------------------------------------------------------------------

    public function test_features_matrix_includes_theme_quota_key(): void
    {
        $matrix = $this->license->getFeaturesMatrix(LicenseService::TIER_PRO);

        $this->assertArrayHasKey('theme_quota', $matrix);
        $this->assertArrayHasKey('premium_themes', $matrix);   // deprecated alias still present
        $this->assertIsArray($matrix['theme_quota']);
        $this->assertArrayHasKey('always_on', $matrix['theme_quota']);
        $this->assertArrayHasKey('max_premium_active', $matrix['theme_quota']);
        $this->assertArrayHasKey('catalog', $matrix['theme_quota']);
    }

    // -----------------------------------------------------------------------
    // ExtensionBootstrapService discovery
    // -----------------------------------------------------------------------

    public function test_theme_pack_discovery_creates_registry_rows(): void
    {
        $bootstrapService = app(\Modules\Core\System\Services\ExtensionBootstrapService::class);
        $bootstrapService->discover();

        foreach (ExtensionFamilyCatalog::firstPartyThemePackSlugs() as $packSlug) {
            $ext = Extension::query()->where('slug', $packSlug)->first();
            $this->assertNotNull($ext, "Expected sys_extensions row for [{$packSlug}] after discover().");
            $this->assertSame('theme', $ext->type, "Expected type=theme for [{$packSlug}].");
            $this->assertSame(ExtensionFamilyCatalog::THEME, $ext->family, "Expected family=theme for [{$packSlug}].");
        }
    }

    public function test_theme_janari_pack_manifest_column_has_theme_flags(): void
    {
        $bootstrapService = app(\Modules\Core\System\Services\ExtensionBootstrapService::class);
        $bootstrapService->discover();

        $janariPack = Extension::query()->where('slug', 'theme-janari')->first();
        $this->assertNotNull($janariPack);

        $manifest = $janariPack->manifest;
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('theme_flags', $manifest);
        $this->assertTrue((bool) ($manifest['theme_flags']['always_on'] ?? false));
        $this->assertTrue((bool) ($manifest['theme_flags']['non_deactivatable'] ?? false));
    }

    public function test_premium_pack_manifest_has_premium_flag(): void
    {
        $bootstrapService = app(\Modules\Core\System\Services\ExtensionBootstrapService::class);
        $bootstrapService->discover();

        $layungPack = Extension::query()->where('slug', 'theme-layung')->first();
        $this->assertNotNull($layungPack);

        $manifest = $layungPack->manifest;
        $this->assertIsArray($manifest);
        $this->assertTrue((bool) ($manifest['theme_flags']['premium'] ?? false));
    }
}
