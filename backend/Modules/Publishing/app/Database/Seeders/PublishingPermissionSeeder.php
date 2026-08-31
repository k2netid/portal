<?php

declare(strict_types=1);

namespace Modules\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * CMS publishing + library permissions (shared manage content for taxonomy UI).
 */
class PublishingPermissionSeeder extends Seeder
{
    public static function ensure(): void
    {
        (new self)->run();
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perms = [
            'view content',
            'create content',
            'edit content',
            'delete content',
            'approve content',
            'publish content',
            'manage content',
            'view categories',
            'manage categories',
            'manage tags',
            'view comments',
            'manage comments',
            'view seo',
            'manage seo',
            'view content templates',
            'manage content templates',
            'edit content templates',
            'delete content templates',
        ];

        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (['super', 'admin', 'editor'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();
            if ($role) {
                $role->givePermissionTo($perms);
            }
        }

        $author = Role::query()
            ->where('name', 'author')
            ->where('guard_name', 'web')
            ->first();
        if ($author) {
            $author->givePermissionTo([
                'view content',
                'create content',
                'edit content',
                'view categories',
                'view comments',
                'view seo',
            ]);
        }
    }
}
