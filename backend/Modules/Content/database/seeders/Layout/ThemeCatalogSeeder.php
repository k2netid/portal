<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Registers bundled themes in lay_themes (scan) and backfills source metadata.
 * Invoked automatically from LayoutDatabaseSeeder during migrate:fresh --seed.
 */
final class ThemeCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('theme:backfill-source', ['--force' => true]);
        Artisan::call('theme:scan-register');

        if ($this->command !== null) {
            $this->command->info(trim(Artisan::output()));
        }
    }
}
