<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix sec_csp_reports
        if (Schema::hasTable('sec_csp_reports')) {
            Schema::table('sec_csp_reports', function (Blueprint $table): void {
                if (! Schema::hasColumn('sec_csp_reports', 'blocked_uri')) {
                    $table->string('blocked_uri')->nullable();
                }
                if (! Schema::hasColumn('sec_csp_reports', 'source_file')) {
                    $table->string('source_file')->nullable();
                }
                if (! Schema::hasColumn('sec_csp_reports', 'line_number')) {
                    $table->integer('line_number')->nullable();
                }
                if (! Schema::hasColumn('sec_csp_reports', 'user_agent')) {
                    $table->text('user_agent')->nullable();
                }
                if (! Schema::hasColumn('sec_csp_reports', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable();
                }
                if (! Schema::hasColumn('sec_csp_reports', 'status')) {
                    $table->string('status')->default('new')->index();
                }
            });
        }

        // Add sec_file_integrity_baselines
        if (! Schema::hasTable('sec_file_integrity_baselines')) {
            Schema::create('sec_file_integrity_baselines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('file_path')->unique();
                $table->string('hash');
                $table->integer('file_size');
                $table->string('status')->default('ok')->index();
                $table->timestamp('checked_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_file_integrity_baselines');
    }
};
