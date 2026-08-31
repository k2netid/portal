<?php

declare(strict_types=1);

namespace Modules\Core\Tests\System\Feature;

use Modules\Core\System\Database\Seeders\CmsRolesSeeder;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Services\ExtensionLifecycleOrchestrator;
use Modules\Newsletter\Database\Seeders\NewsletterDefaultsSeeder;
use Tests\TestCase;

class ExtensionLifecycleOrchestratorTest extends TestCase
{
    public function test_runs_manifest_seeders_on_activate(): void
    {
        (new CmsRolesSeeder)->run();

        $extension = Extension::query()->updateOrCreate(
            ['slug' => 'member'],
            [
                'type' => 'module',
                'name' => 'Member',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_core' => false,
                'manifest' => json_decode(
                    (string) file_get_contents(base_path('Modules/Member/manifest.json')),
                    true,
                ),
            ],
        );

        $ran = app(ExtensionLifecycleOrchestrator::class)->runActivateSeeders($extension);

        $this->assertContains('Modules\\Member\\Database\\Seeders\\MemberPermissionSeeder', $ran);

        $admin = Role::query()->where('name', 'admin')->where('guard_name', 'web')->firstOrFail();
        $this->assertTrue($admin->hasPermissionTo(Permission::findByName('view members', 'web')));
        $this->assertTrue($admin->hasPermissionTo(Permission::findByName('manage members', 'web')));
    }

    public function test_newsletter_defaults_seeder_sets_general_list_name(): void
    {
        $extension = Extension::query()->updateOrCreate(
            ['slug' => 'newsletter'],
            [
                'type' => 'module',
                'name' => 'Newsletter',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
                'settings' => [],
            ],
        );

        NewsletterDefaultsSeeder::ensure();

        $settings = $extension->fresh()?->settings;
        $this->assertIsArray($settings);
        $this->assertSame('General', $settings['default_list_name'] ?? null);
    }
}
