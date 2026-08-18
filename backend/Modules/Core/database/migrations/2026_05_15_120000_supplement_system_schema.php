<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_languages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('native_name');
            $table->string('flag', 16)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sys_translations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('translatable_type')->index();
            $table->uuid('translatable_id')->index();
            $table->string('language_code', 10)->index();
            $table->string('field')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'language_code', 'field'], 'sys_translations_unique');
        });

        Schema::create('sys_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('sys_scheduled_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('command');
            $table->string('schedule');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->text('last_output')->nullable();
            $table->timestamps();
        });

        Schema::create('sys_redis_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('connection');
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        Schema::create('sys_email_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('subject');
            $table->longText('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sys_content_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->json('meta')->nullable();
            $table->uuid('category_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sys_field_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('applies_to')->nullable();
            $table->json('conditions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sys_custom_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('field_group_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('type')->default('text');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('field_group_id')->references('id')->on('sys_field_groups')->nullOnDelete();
        });

        Schema::create('sys_content_custom_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('custom_field_id')->index();
            $table->string('model_type');
            $table->uuid('model_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->foreign('custom_field_id')->references('id')->on('sys_custom_fields')->cascadeOnDelete();
        });

        Schema::create('sys_two_factor_auth', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->text('secret');
            $table->json('recovery_codes')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_two_factor_auth');
        Schema::dropIfExists('sys_content_custom_fields');
        Schema::dropIfExists('sys_custom_fields');
        Schema::dropIfExists('sys_field_groups');
        Schema::dropIfExists('sys_content_templates');
        Schema::dropIfExists('sys_email_templates');
        Schema::dropIfExists('sys_redis_settings');
        Schema::dropIfExists('sys_scheduled_tasks');
        Schema::dropIfExists('sys_notifications');
        Schema::dropIfExists('sys_translations');
        Schema::dropIfExists('sys_languages');
    }
};
