<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeders\System;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\Plugin;

class BeforeFooterPromoPluginSeeder extends Seeder
{
    public function run(): void
    {
        Plugin::updateOrCreate(
            ['slug' => 'before-footer-promo'],
            [
                'name' => 'Before Footer Promo',
                'version' => '1.0.0',
                'description' => 'Promo app block above site footer (theme slot).',
                'author' => 'Jejakawan',
                'is_active' => false,
                'priority' => 30,
                'settings' => [
                    'theme_blocks' => [
                        ['slot' => 'before_footer'],
                    ],
                ],
            ],
        );
    }
}
