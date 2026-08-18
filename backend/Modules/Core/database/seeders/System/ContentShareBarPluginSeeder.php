<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeders\System;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Plugin;

/**
 * Bundled demo plugin for Fase 2 App Blocks (after_post_content slot).
 */
class ContentShareBarPluginSeeder extends Seeder
{
    public function run(): void
    {
        Plugin::updateOrCreate(
            ['slug' => 'content-share-bar'],
            [
                'name' => 'Content Share Bar',
                'version' => '1.0.0',
                'description' => 'Share CTA app block below article body (theme slot).',
                'author' => 'Jejakawan',
                'is_active' => true,
                'priority' => 20,
                'settings' => [
                    'theme_blocks' => [
                        ['slot' => 'after_post_content', 'priority' => 20],
                    ],
                ],
            ],
        );
    }
}
