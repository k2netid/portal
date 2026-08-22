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
        if (! Schema::hasTable('infra_deleted_files')) {
            return;
        }

        Schema::table('infra_deleted_files', function (Blueprint $table): void {
            if (! Schema::hasColumn('infra_deleted_files', 'trash_path')) {
                $table->string('trash_path')->nullable()->index()->after('original_path');
            }
            if (! Schema::hasColumn('infra_deleted_files', 'name')) {
                $table->string('name')->nullable()->after('disk');
            }
            if (! Schema::hasColumn('infra_deleted_files', 'type')) {
                $table->string('type')->default('file')->after('name');
            }
            if (! Schema::hasColumn('infra_deleted_files', 'extension')) {
                $table->string('extension', 50)->nullable()->after('size');
            }
        });

        // Ensure deleted_by exists as UUID
        if (! Schema::hasColumn('infra_deleted_files', 'deleted_by')) {
            Schema::table('infra_deleted_files', function (Blueprint $table): void {
                $table->uuid('deleted_by')->nullable()->index()->after('mime_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('infra_deleted_files')) {
            Schema::table('infra_deleted_files', function (Blueprint $table): void {
                if (Schema::hasColumn('infra_deleted_files', 'trash_path')) {
                    $table->dropColumn('trash_path');
                }
                if (Schema::hasColumn('infra_deleted_files', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('infra_deleted_files', 'type')) {
                    $table->dropColumn('type');
                }
                if (Schema::hasColumn('infra_deleted_files', 'extension')) {
                    $table->dropColumn('extension');
                }
            });
        }
    }
};
