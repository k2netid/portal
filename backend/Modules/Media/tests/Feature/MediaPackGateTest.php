<?php

declare(strict_types=1);

namespace Modules\Media\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Tests\TestCase;

class MediaPackGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_manage_media_forbidden_when_pack_inactive(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/media')
            ->assertForbidden();
    }

    public function test_manage_media_ok_when_pack_active(): void
    {
        $this->activatePack('media');
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/media')
            ->assertOk();
    }

    public function test_manage_media_forbidden_after_deactivate(): void
    {
        $this->activatePack('media');
        $admin = $this->createAdminUser();
        Extension::query()->where('slug', 'media')->update(['status' => 'inactive']);
        Extension::flushProductActiveMemo();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/media')
            ->assertForbidden();
    }
}
