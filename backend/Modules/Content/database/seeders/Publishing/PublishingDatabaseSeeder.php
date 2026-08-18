<?php

namespace Modules\Content\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

class PublishingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Sample Pages (Themes handled by Layout module)
        $pages = [
            [
                'title' => 'Welcome to Jejakawan',
                'slug' => 'home',
                'body' => '<h1>Welcome</h1><p>This is your new home page.</p>',
                'type' => 'page',
                'status' => 'published',
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'body' => '<h1>About</h1><p>We are a modular platform.</p>',
                'type' => 'page',
                'status' => 'published',
            ],
        ];

        $admin = User::first();
        if (! $admin) {
            return;
        }

        foreach ($pages as $page) {
            Content::updateOrCreate(
                ['slug' => $page['slug'], 'type' => 'page'],
                array_merge($page, ['author_id' => $admin->id])
            );
        }

        // 2. Call Extended Content Seeders
        $this->call([
            StudioSeeder::class,
            SampleContentSeeder::class,
        ]);
    }
}
