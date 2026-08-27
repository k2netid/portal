<?php

namespace Tests;

use Illuminate\Auth\RequestGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Hapus artefak bootstrap/cache agar phpunit.xml (sqlite :memory:, dll.) dipakai,
     * bukan konfigurasi hasil `config:cache` dari lingkungan dev (mis. pgsql).
     */
    protected static function purgeBootstrapCachesBeforeApplicationBoot(): void
    {
        $dir = dirname(__DIR__).'/bootstrap/cache';
        if (! is_dir($dir)) {
            return;
        }

        $files = glob($dir.'/*.php');
        if (is_array($files)) {
            foreach ($files as $path) {
                @unlink($path);
            }
        }
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        static::purgeBootstrapCachesBeforeApplicationBoot();

        parent::setUp();

        // Initialize session for tests that need it (login, etc.)
        $this->withSession([]);

        // Add viaRemember macro to RequestGuard for AuthenticateSession compatibility
        RequestGuard::macro('viaRemember', function () {
            return false; // Token-based auth never uses "remember me"
        });
    }

    /**
     * Seed permissions and roles for testing.
     */
    protected function seedPermissionsAndRoles(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Flush cache to reset rate limiters
        Cache::flush();

        // If permissions already exist, we don't need to seed them again
        if (Permission::exists()) {
            return;
        }

        // Create permissions if they don't exist
        $permissions = [
            // Content
            'view content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'manage content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories', 'manage categories',
            'view tags', 'create tags', 'edit tags', 'delete tags', 'manage tags',
            'view seo', 'edit seo', 'manage seo',
            // Media
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',
            'view files', 'upload files', 'edit files', 'delete files', 'manage files',
            // Engagement
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments', 'manage comments',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'manage forms',
            'view newsletter', 'manage newsletter',
            'view members', 'manage members',
            // Users & Roles
            'view users', 'create users', 'edit users', 'delete users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles', 'manage roles', 'manage permissions',
            // Appearance
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view menus', 'create menus', 'edit menus', 'delete menus', 'manage menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets', 'manage widgets',
            // System & Settings
            'view settings', 'manage settings', 'manage security operations',
            'manage kyc reviews',
            'manage security logs', 'manage security ip-lists', 'manage security integrity', 'manage security maintenance',
            'view plugins', 'install plugins', 'edit plugins', 'delete plugins', 'manage plugins',
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',
            'view scheduled tasks', 'manage scheduled tasks',
            'view backups', 'create backups', 'download backups', 'delete backups', 'manage backups',
            'view system', 'manage system',
            // Logs & Analytics
            'view logs', 'delete logs',
            'view analytics',
            'manage search',
            'view activity logs',
            'view security logs',
            // JA-Mail
            'use mail',
            'manage personal mail account',
            'manage multi mail accounts',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create admin and super roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $superRole = Role::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);
        $superRole->givePermissionTo(Permission::all());
    }

    /**
     * Create an authenticated user for testing.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create an admin user for testing.
     */
    protected function createAdminUser(array $attributes = []): User
    {
        $user = $this->createUser($attributes);
        $user->assignRole('admin');

        return $user;
    }

    /**
     * Create a super admin user for testing.
     */
    protected function createSuperAdminUser(array $attributes = []): User
    {
        $user = $this->createUser($attributes);
        $user->assignRole('super');

        return $user;
    }

    /**
     * Create a creator user for testing.
     */
    protected function createCreatorUser(array $attributes = []): User
    {
        $user = $this->createUser($attributes);
        $role = Role::firstOrCreate(['name' => 'creator', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }

    /**
     * Create a viewer user for testing.
     */
    protected function createViewerUser(array $attributes = []): User
    {
        $user = $this->createUser($attributes);
        $role = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }

    /**
     * Act as a user for testing.
     */
    protected function actingAsUser(?User $user = null): self
    {
        $user = $user ?? $this->createUser();

        return $this->actingAs($user, 'sanctum');
    }

    /**
     * Act as an admin user for testing.
     */
    protected function actingAsAdmin(?User $user = null): self
    {
        $user = $user ?? $this->createAdminUser();

        return $this->actingAs($user, 'sanctum');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function activatePack(string $slug, array $extra = []): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => $slug],
            array_merge([
                'type' => 'module',
                'name' => ucfirst($slug),
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
            ], $extra),
        );
        Extension::flushProductActiveMemo();
    }
}
