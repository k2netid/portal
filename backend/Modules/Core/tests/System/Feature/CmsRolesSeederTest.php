<?php

declare(strict_types=1);

namespace Modules\Core\Tests\System\Feature;

use Modules\Core\System\Database\Seeders\CmsRolesSeeder;
use Modules\Core\System\Database\Seeders\FoundationSeeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Modules\Publishing\Database\Seeders\PublishingPermissionSeeder;
use Tests\TestCase;

class CmsRolesSeederTest extends TestCase
{
    public function test_cms_roles_seeder_creates_editorial_roles(): void
    {
        (new CmsRolesSeeder)->run();

        foreach (['admin', 'editor', 'author', 'operator'] as $roleName) {
            $this->assertNotNull(
                Role::query()->where('name', $roleName)->where('guard_name', 'web')->first(),
                "Missing role: {$roleName}",
            );
        }
    }

    public function test_foundation_seeder_creates_cms_roles_for_cms_site_profile(): void
    {
        config(['install.profile' => 'cms_site']);

        $this->seed(FoundationSeeder::class);

        $this->assertNotNull(Role::query()->where('name', 'admin')->where('guard_name', 'web')->first());
        $this->assertNotNull(Role::query()->where('name', 'editor')->where('guard_name', 'web')->first());
        $this->assertNotNull(Role::query()->where('name', 'author')->where('guard_name', 'web')->first());
    }

    public function test_publishing_permissions_attach_to_cms_roles_when_present(): void
    {
        (new CmsRolesSeeder)->run();
        (new PublishingPermissionSeeder)->run();

        $editor = Role::query()->where('name', 'editor')->where('guard_name', 'web')->firstOrFail();
        $this->assertTrue($editor->hasPermissionTo('view content'));

        $author = Role::query()->where('name', 'author')->where('guard_name', 'web')->firstOrFail();
        $this->assertTrue($author->hasPermissionTo('create content'));
        $this->assertFalse($author->hasPermissionTo('publish content'));

        $admin = Role::query()->where('name', 'admin')->where('guard_name', 'web')->firstOrFail();
        $this->assertTrue($admin->hasPermissionTo(Permission::findByName('manage content', 'web')));
    }
}
