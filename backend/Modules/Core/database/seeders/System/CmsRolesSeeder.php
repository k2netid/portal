<?php

declare(strict_types=1);

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * CMS editorial roles expected by pack PermissionSeeders (admin, editor, author).
 * Idempotent — safe on fresh seed, install profile apply, and extension activate heal.
 */
class CmsRolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $author = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        $admin->syncPermissions(Permission::whereIn('name', [
            'view profile', 'edit profile',
            'view settings', 'manage settings',
            'manage module access',
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',
            'view analytics',
            'view redirects', 'manage redirects',
            'use mail',
            'manage personal mail account',
            'manage multi mail accounts',
        ])->get());

        $editor->syncPermissions(Permission::whereIn('name', [
            'view profile', 'edit profile',
            'view media', 'upload media', 'edit media',
            'view analytics',
            'use mail',
            'manage personal mail account',
        ])->get());

        $author->syncPermissions(Permission::whereIn('name', [
            'view profile', 'edit profile',
            'view media', 'upload media',
        ])->get());

        if ($this->command) {
            $this->command->info('CMS roles seeded (admin, editor, author, operator).');
        }
    }
}
