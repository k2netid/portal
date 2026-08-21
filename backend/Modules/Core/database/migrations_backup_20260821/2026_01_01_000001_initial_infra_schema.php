<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Backups Registry
        Schema::create('infra_backups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('disk');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('full'); // full, database, files
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('password')->nullable();
            $table->timestamps();
        });

        // 2. Deleted Files (Recycle Bin)
        Schema::create('infra_deleted_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('original_name');
            $table->string('original_path');
            $table->string('disk');
            $table->unsignedBigInteger('size');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 3. Webhooks Registry
        Schema::create('infra_webhooks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('url');
            $table->string('event')->index();
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Domain Redirects
        Schema::create('infra_redirects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('from_domain')->index();
            $table->string('to_domain');
            $table->string('target_path')->nullable();
            $table->integer('status_code')->default(301);
            $table->boolean('keep_path')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['from_domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infra_redirects');
        Schema::dropIfExists('infra_webhooks');
        Schema::dropIfExists('infra_deleted_files');
        Schema::dropIfExists('infra_backups');
    }
};
