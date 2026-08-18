<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Seeders;

use Illuminate\Database\Seeder;

class LayoutDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ThemeSeeder::class,
            MenuLocationStandardizationSeeder::class,
            JanariHubThemeSettingsSeeder::class,
            ThemeCatalogSeeder::class,
        ]);
    }
}
