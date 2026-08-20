<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Layout\Database\Seeders\LayoutDatabaseSeeder;
use Modules\Content\Publishing\Database\Seeders\PublishingDatabaseSeeder;
use Modules\Core\System\Database\Seeders\DataModelDemoSeeder;
use Modules\Core\System\Database\Seeders\SystemDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SystemDatabaseSeeder::class);
        $this->call(DataModelDemoSeeder::class);
        $this->call(LayoutDatabaseSeeder::class);
        $this->call(PublishingDatabaseSeeder::class);
    }
}
