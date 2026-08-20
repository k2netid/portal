<?php

declare(strict_types=1);

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\ContentTemplate;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Models\EmailTemplate;
use Modules\Core\System\Models\User;

class DataModelDemoSeeder extends Seeder
{
    public function run(): void
    {
        $type = ContentType::updateOrCreate(
            ['slug' => 'announcements'],
            [
                'name' => 'Announcements',
                'description' => 'Demo data model schema for console dynamic records UI',
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
                    'body' => 'Sample announcement created by DataModelDemoSeeder.',
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
                    'content_template' => '<p>{{ content }}</p>',
                    'meta_template' => ['category' => 'General'],
                    'is_active' => true,
                    'created_by' => $admin->id,
                ],
            );

            EmailTemplate::updateOrCreate(
                ['slug' => 'welcome-user'],
                [
                    'name' => 'Welcome User Email',
                    'description' => 'Default transactional welcome email',
                    'subject' => 'Welcome to Jejakawan!',
                    'body_html' => '<p>Hello {{ name }}, welcome to your account.</p>',
                    'body_plain' => 'Hello {{ name }}, welcome to your account.',
                    'variables' => ['name', 'email', 'app_name'],
                    'category' => 'auth',
                    'is_system' => true,
                    'is_active' => true,
                    'created_by' => $admin->id,
                ],
            );
        }
    }
}
