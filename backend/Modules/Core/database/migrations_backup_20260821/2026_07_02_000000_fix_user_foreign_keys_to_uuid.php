<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix sec_ip_lists
        if (Schema::hasTable('sec_ip_lists')) {
            Schema::table('sec_ip_lists', function (Blueprint $table): void {
                if (Schema::hasColumn('sec_ip_lists', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
            Schema::table('sec_ip_lists', function (Blueprint $table): void {
                $table->uuid('created_by')->nullable();
            });
        }

        // Fix infra_deleted_files
        if (Schema::hasTable('infra_deleted_files')) {
            Schema::table('infra_deleted_files', function (Blueprint $table): void {
                if (Schema::hasColumn('infra_deleted_files', 'deleted_by')) {
                    $table->dropColumn('deleted_by');
                }
            });
            Schema::table('infra_deleted_files', function (Blueprint $table): void {
                $table->uuid('deleted_by')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sec_ip_lists')) {
            Schema::table('sec_ip_lists', function (Blueprint $table): void {
                if (Schema::hasColumn('sec_ip_lists', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
            Schema::table('sec_ip_lists', function (Blueprint $table): void {
                $table->unsignedBigInteger('created_by')->nullable();
            });
        }

        if (Schema::hasTable('infra_deleted_files')) {
            Schema::table('infra_deleted_files', function (Blueprint $table): void {
                if (Schema::hasColumn('infra_deleted_files', 'deleted_by')) {
                    $table->dropColumn('deleted_by');
                }
            });
            Schema::table('infra_deleted_files', function (Blueprint $table): void {
                $table->unsignedBigInteger('deleted_by')->nullable();
            });
        }
    }
};
