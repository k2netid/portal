<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Media Folders
        Schema::create('srv_media_folders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->nullable()->index();
            $table->uuid('parent_id')->nullable()->index();
            $table->uuid('author_id')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_shared')->default(false);
            $table->string('module')->default('publishing')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('srv_media_folders', function (Blueprint $table): void {
            $table->foreign('parent_id')->references('id')->on('srv_media_folders')->onDelete('cascade');
        });

        // 2. Media Files
        Schema::create('srv_media_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('folder_id')->nullable()->index();
            $table->uuid('author_id')->nullable()->index();
            $table->string('file_name');
            $table->string('name');
            $table->string('mime_type')->index();
            $table->unsignedBigInteger('size');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('extension', 10)->nullable();
            $table->string('alt')->nullable();
            $table->string('description')->nullable();
            $table->string('caption')->nullable();
            $table->json('metadata')->nullable();
            $table->string('module')->default('publishing')->index();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('folder_id')->references('id')->on('srv_media_folders')->onDelete('set null');
        });

        // 3. Deleted files audit (trash tracking)
        Schema::create('srv_media_deleted_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('original_path');
            $table->string('trash_path')->index();
            $table->string('disk')->default('public');
            $table->string('name')->nullable();
            $table->string('type')->default('file');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('extension', 20)->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        // 4. Media Usages
        Schema::create('srv_media_usages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('file_id')->index();
            $table->string('model_type')->index();
            $table->uuid('model_id')->index();
            $table->string('field_name')->nullable()->index();
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('srv_media_files')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srv_media_usages');
        Schema::dropIfExists('srv_media_deleted_files');
        Schema::dropIfExists('srv_media_files');
        Schema::dropIfExists('srv_media_folders');
    }
};
