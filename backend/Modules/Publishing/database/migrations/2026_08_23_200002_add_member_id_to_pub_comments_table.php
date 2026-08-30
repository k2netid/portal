<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pub_comments', function (Blueprint $table): void {
            if (! Schema::hasColumn('pub_comments', 'member_id')) {
                $table->uuid('member_id')->nullable()->index()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pub_comments', function (Blueprint $table): void {
            if (Schema::hasColumn('pub_comments', 'member_id')) {
                $table->dropColumn('member_id');
            }
        });
    }
};
