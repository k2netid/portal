<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\Core\System\Models\ContentType;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sys_content_types')) {
            return;
        }

        ContentType::grandfatherReservedSlugs();
    }

    public function down(): void
    {
        // One-way rename; original reserved slugs must not be restored.
    }
};
