<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Database\Seeders\DataModelDemoSeeder;
use Modules\Core\System\Database\Seeders\SystemDatabaseSeeder;
use Modules\Core\System\Services\InstallProfileApplicator;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SystemDatabaseSeeder::class);
        $this->call(DataModelDemoSeeder::class);

        // Durable product boot: Core-only vs CMS vs public Site — no tinker after migrate:fresh.
        $result = app(InstallProfileApplicator::class)->apply();
        $this->command?->info(sprintf(
            'Install profile [%s]: discovered=%d activated=%s theme=%s',
            $result['profile'],
            $result['discovered'],
            implode(',', $result['activated']) ?: 'none',
            $result['themes']['active'] ?? '—'
        ));
        foreach ($result['errors'] as $error) {
            $this->command?->warn($error);
        }
    }
}
