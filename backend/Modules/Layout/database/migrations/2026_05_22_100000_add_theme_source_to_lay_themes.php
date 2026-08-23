<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lay_themes', function (Blueprint $table): void {
            $table->string('source', 32)->default('bundled')->after('path');
            $table->string('bundle_url')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('lay_themes', function (Blueprint $table): void {
            $table->dropColumn(['source', 'bundle_url']);
        });
    }
};
