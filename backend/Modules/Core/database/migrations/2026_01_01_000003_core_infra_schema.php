<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Data Models (ContentTypes) & Dynamic Records
        Schema::create('sys_content_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sys_dynamic_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('content_type_id')->index();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('content_type_id')
                ->references('id')
                ->on('sys_content_types')
                ->cascadeOnDelete();
        });

        // 2. Scheduled Tasks (Crons)
        Schema::create('sys_scheduled_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('command');
            $table->string('schedule');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->string('status')->nullable();
            $table->text('output')->nullable();
            $table->text('last_output')->nullable();
            $table->string('last_run_status')->nullable();
            $table->decimal('last_run_duration', 8, 2)->nullable();
            $table->timestamps();
        });

        // 3. Automated Backups
        Schema::create('infra_backups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('disk')->default('local');
            $table->string('path')->default('');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('full');
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('password')->nullable();
            $table->timestamps();
        });

        // 4. Deleted Files (Recycle Bin)
        Schema::create('infra_deleted_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('original_path');
            $table->string('trash_path')->index();
            $table->string('disk')->default('public');
            $table->string('name')->nullable();
            $table->string('type')->default('file');
            $table->unsignedBigInteger('size')->default(0)->nullable();
            $table->string('extension', 50)->nullable();
            $table->string('mime_type')->nullable();
            $table->uuid('deleted_by')->nullable()->index();
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 5. Webhooks Dispatcher & Deliveries
        Schema::create('infra_webhooks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('url');
            $table->string('event')->default('*')->index();
            $table->json('events')->nullable();
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('headers')->nullable();
            $table->integer('retry_limit')->default(3);
            $table->integer('timeout')->default(30);
            $table->timestamps();
        });

        Schema::create('infra_webhook_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('webhook_id')->index();
            $table->string('event')->index();
            $table->json('payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->string('status')->default('success')->index();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->integer('attempt')->default(1);
            $table->timestamps();

            $table->foreign('webhook_id')->references('id')->on('infra_webhooks')->cascadeOnDelete();
        });

        // 6. URL / Domain Redirects
        Schema::create('infra_redirects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('from_domain')->nullable()->index();
            $table->string('to_domain')->nullable();
            $table->string('target_path')->nullable();
            $table->string('source_path')->nullable()->index();
            $table->integer('status_code')->default(301);
            $table->boolean('keep_path')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infra_redirects');
        Schema::dropIfExists('infra_webhook_deliveries');
        Schema::dropIfExists('infra_webhooks');
        Schema::dropIfExists('infra_deleted_files');
        Schema::dropIfExists('infra_backups');
        Schema::dropIfExists('sys_scheduled_tasks');
        Schema::dropIfExists('sys_dynamic_records');
        Schema::dropIfExists('sys_content_types');
    }
};
