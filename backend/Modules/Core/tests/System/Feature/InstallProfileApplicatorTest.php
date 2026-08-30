<?php

declare(strict_types=1);

namespace Modules\Core\Tests\System\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Services\InstallProfileApplicator;
use Tests\TestCase;

class InstallProfileApplicatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_site_profile_activates_site_and_layout(): void
    {
        config([
            'install.profile' => 'cms_site',
            'install.skip_license_checks' => true,
        ]);

        $result = app(InstallProfileApplicator::class)->apply('cms_site');

        $this->assertSame('cms_site', $result['profile']);
        $this->assertGreaterThan(0, $result['discovered']);
        $this->assertTrue(Extension::isProductActive('site'));
        $this->assertTrue(Extension::isProductActive('layout'));
        $this->assertTrue(Extension::isProductActive('publishing'));
    }

    public function test_core_profile_leaves_site_inactive(): void
    {
        config([
            'install.profile' => 'core',
            'install.skip_license_checks' => true,
        ]);

        app(InstallProfileApplicator::class)->apply('core');

        // Discover creates inactive Site row; product gate must stay off.
        $this->assertFalse(Extension::isProductActive('site'));
    }
}
