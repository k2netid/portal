<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lib_taggables')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE lib_taggables ALTER COLUMN taggable_id TYPE uuid USING taggable_id::uuid');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('lib_taggables')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE lib_taggables ALTER COLUMN taggable_id TYPE varchar(255) USING taggable_id::text');
        }
    }
};
