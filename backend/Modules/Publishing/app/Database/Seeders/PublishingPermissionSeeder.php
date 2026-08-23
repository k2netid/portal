<?php

declare(strict_types=1);

namespace Modules\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * CMS publishing + library permissions (shared manage content for taxonomy UI).
 */
class PublishingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'view content',
            'create content',
            'edit content',
            'delete content',
            'approve content',
            'view categories',
            'manage categories',
            'manage tags',
            'view comments',
            'manage comments',
            'view seo',
            'manage seo',
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
