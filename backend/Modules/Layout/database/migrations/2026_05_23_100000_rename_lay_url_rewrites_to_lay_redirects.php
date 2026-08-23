<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lay_url_rewrites') && ! Schema::hasTable('lay_redirects')) {
            Schema::rename('lay_url_rewrites', 'lay_redirects');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lay_redirects') && ! Schema::hasTable('lay_url_rewrites')) {
            Schema::rename('lay_redirects', 'lay_url_rewrites');
        }
    }
};
