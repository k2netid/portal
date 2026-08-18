<?php

declare(strict_types=1);

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\ContentTemplate;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Models\EmailTemplate;
use Modules\Core\System\Models\User;

class CckDemoSeeder extends Seeder
{
    public function run(): void
    {
        $type = ContentType::updateOrCreate(
            ['slug' => 'announcements'],
            [
                'name' => 'Announcements',
                'description' => 'Demo CCK type for console dynamic records UI',
                'is_active' => true,
                'fields' => [
                    [
                        'name' => 'Title',
                        'slug' => 'title',
                        'type' => 'text',
                        'is_required' => true,
                    ],
                    [
                        'name' => 'Body',
                        'slug' => 'body',
                        'type' => 'longtext',
                        'is_required' => false,
                    ],
                    [
                        'name' => 'Published',
                        'slug' => 'published',
                        'type' => 'boolean',
                        'is_required' => false,
                    ],
                ],
            ],
        );

        if (! DynamicRecord::query()->where('content_type_id', $type->id)->exists()) {
            DynamicRecord::create([
                'content_type_id' => $type->id,
                'data' => [
                    'title' => 'Welcome to Jejakawan',
                    'body' => 'Sample announcement created by CckDemoSeeder.',
                    'published' => true,
                ],
            ]);
        }
        $admin = User::first();
        if ($admin) {
            ContentTemplate::updateOrCreate(
                ['slug' => 'demo-article'],
                [
                    'name' => 'Demo Article Template',
                    'description' => 'Sample content template for console e2e',
                    'type' => 'post',
                    'title_template' => '{{ title }}',
                    'body_template' => '<p>{{ body }}</p>',
                    'is_active' => true,
                    'author_id' => $admin->id,
                ]
            );

            EmailTemplate::updateOrCreate(
                ['slug' => 'welcome-email'],
                [
                    'name' => 'Welcome Email',
                    'subject' => 'Welcome to {{ site_name }}',
                    'body' => '<p>Hello {{ name }}, welcome aboard.</p>',
                    'text_body' => 'Hello {{ name }}, welcome aboard.',
                    'category' => 'system',
                    'is_active' => true,
                ]
            );
        }
    }
}
