<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mem_members', function (Blueprint $table): void {
            if (! Schema::hasColumn('mem_members', 'phone')) {
                $table->string('phone', 32)->nullable()->after('email');
            }
            if (! Schema::hasColumn('mem_members', 'avatar')) {
                $table->string('avatar', 512)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('mem_members', 'bio')) {
                $table->text('bio')->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('mem_members', 'locale')) {
                $table->string('locale', 10)->nullable()->after('bio');
            }
            if (! Schema::hasColumn('mem_members', 'timezone')) {
                $table->string('timezone', 64)->nullable()->after('locale');
            }
            if (! Schema::hasColumn('mem_members', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mem_members', function (Blueprint $table): void {
            $columns = ['phone', 'avatar', 'bio', 'locale', 'timezone', 'last_login_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('mem_members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
