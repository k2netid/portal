<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Security Logs
        Schema::create('sec_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('severity')->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 2. IP Lists (Whitelist/Blocklist)
        Schema::create('sec_ip_lists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45)->index();
            $table->enum('type', ['whitelist', 'blocklist'])->default('blocklist');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['ip_address', 'type']);
        });

        // 3. CSP Reports
        Schema::create('sec_csp_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('document_uri');
            $table->string('referrer')->nullable();
            $table->string('violated_directive');
            $table->string('effective_directive')->nullable();
            $table->string('original_policy')->nullable();
            $table->string('disposition')->nullable();
            $table->string('status_code')->nullable();
            $table->json('raw_report')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_csp_reports');
        Schema::dropIfExists('sec_ip_lists');
        Schema::dropIfExists('sec_logs');
    }
};
