<?php

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Media\Models\Folder;
use Modules\Core\System\Models\User;

class InfrastructureSeeder extends Seeder
{
    /**
     * Run the infrastructure seeds.
     */
    public function run(): void
    {
        $adminEmail = config('app.super_admin_email');
        if (! is_string($adminEmail) || $adminEmail === '') {
            $adminEmail = 'super@jejakawan.com';
        }

        $admin = User::where('email', $adminEmail)->first();
        if (! $admin) {
            return;
        }

        // 1. Standard Global Tags
        $tags = ['Education', 'Technology', 'Jejakawan', 'Announcement'];
        foreach ($tags as $tag) {
            $slug = Str::slug($tag);
            $tagModel = Tag::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $tag,
                    'author_id' => $admin->id,
                ]
            );
            if ($tagModel->trashed()) {
                $tagModel->restore();
            }
        }

        // 2. Standard Media Folders
        $folders = [
            ['name' => 'Logos', 'slug' => 'logos', 'module' => 'system'],
            ['name' => 'Documents', 'slug' => 'documents', 'module' => 'system'],
        ];

        foreach ($folders as $folder) {
            Folder::withTrashed()->updateOrCreate(
                ['slug' => $folder['slug']],
                array_merge($folder, [
                    'author_id' => $admin->id,
                    'is_shared' => true,
                    'sort_order' => 0,
                ])
            );
        }

        $this->command->info('Global infrastructure seeded successfully!');
    }
}
