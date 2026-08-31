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
        $this->assertTrue(Extension::isProductActive('member'));
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

    public function test_cms_profile_deactivates_site_when_cms_site_was_active(): void
    {
        config([
            'install.skip_license_checks' => true,
        ]);

        app(InstallProfileApplicator::class)->apply('cms_site');
        $this->assertTrue(Extension::isProductActive('site'));
        $this->assertTrue(Extension::isProductActive('layout'));

        $result = app(InstallProfileApplicator::class)->apply('cms');

        $this->assertSame('cms', $result['profile']);
        $this->assertContains('site', $result['deactivated']);
        $this->assertFalse(Extension::isProductActive('site'));
        $this->assertTrue(Extension::isProductActive('layout'));
    }

    public function test_preview_cms_lists_site_deactivation_when_site_active(): void
    {
        Extension::create([
            'slug' => 'site',
            'type' => 'module',
            'family' => 'audience',
            'name' => 'Site',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        $preview = app(InstallProfileApplicator::class)->preview('cms');

        $this->assertSame('enforce', $preview['contract']);
        $this->assertContains('cms_will_disable_site', $preview['warnings']);
        $this->assertSame(['site'], array_column($preview['will_deactivate'], 'slug'));
    }

    public function test_preview_core_lists_pack_deactivation(): void
    {
        Extension::create([
            'slug' => 'layout',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Layout',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        $preview = app(InstallProfileApplicator::class)->preview('core');

        $this->assertTrue($preview['can_apply']);
        $this->assertContains('core_will_disable_packs', $preview['warnings']);
        $this->assertContains('layout', array_column($preview['will_deactivate'], 'slug'));
    }
}
