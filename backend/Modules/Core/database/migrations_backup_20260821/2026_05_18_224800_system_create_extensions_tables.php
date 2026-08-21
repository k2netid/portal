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
        if (! Schema::hasTable('sys_extensions')) {
            Schema::create('sys_extensions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('slug', 100)->unique();
                $table->string('type', 20); // 'module' or 'plugin'
                $table->string('name', 150);
                $table->string('version', 30);
                $table->string('database_version', 30);
                $table->string('status', 20)->default('inactive'); // 'active', 'inactive', 'broken', 'updating'
                $table->boolean('is_core')->default(false);
                $table->string('author', 100)->nullable();
                $table->string('license', 50)->default('MIT');
                $table->json('requirements')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('sys_extension_logs')) {
            Schema::create('sys_extension_logs', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('extension_slug', 100);
                $table->string('action', 30); // 'install', 'uninstall', 'activate', 'deactivate', 'upgrade', 'rollback'
                $table->string('version_before', 30)->nullable();
                $table->string('version_after', 30)->nullable();
                $table->string('status', 20); // 'success', 'failed'
                $table->text('error_message')->nullable();
                $table->uuid('performed_by')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_extension_logs');
        Schema::dropIfExists('sys_extensions');
    }
};
