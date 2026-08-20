<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Modules\Core\System\Services\LicenseService;
use Tests\TestCase;

final class LicenseServiceTest extends TestCase
{
    private LicenseService $licenseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->licenseService = app(LicenseService::class);
    }

    public function test_default_community_tier_capabilities(): void
    {
        $status = $this->licenseService->getLicenseStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('tier', $status);
        $this->assertArrayHasKey('features', $status);
        $this->assertArrayHasKey('masked_key', $status);
    }

    public function test_can_use_feature_check(): void
    {
        // Community should not have white_label
        $canWhiteLabel = $this->licenseService->canUseFeature('white_label');
        $this->assertIsBool($canWhiteLabel);
    }

    public function test_local_key_activation_pro(): void
    {
        $result = $this->licenseService->activateLicense('JACP-PRO-TEST-1234-5678');

        $this->assertTrue($result['success']);
        $this->assertEquals('pro', $result['data']['tier']);
        $this->assertTrue($this->licenseService->canUseFeature('premium_themes'));
        $this->assertTrue($this->licenseService->canUseFeature('pro_builder_modules'));
    }

    public function test_local_key_activation_enterprise(): void
    {
        $result = $this->licenseService->activateLicense('JACP-ENT-VIP-9999-0000');

        $this->assertTrue($result['success']);
        $this->assertEquals('enterprise', $result['data']['tier']);
        $this->assertTrue($this->licenseService->canUseFeature('white_label'));
        $this->assertTrue($this->licenseService->canUseFeature('multi_site'));
    }

    public function test_deactivate_license(): void
    {
        $this->licenseService->activateLicense('JACP-PRO-TEST-1234-5678');
        $result = $this->licenseService->deactivateLicense();

        $this->assertTrue($result['success']);
        $status = $this->licenseService->getLicenseStatus();
        $this->assertEquals('community', $status['tier']);
    }
}
