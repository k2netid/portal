<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Auth Users
        Schema::create('srv_auth_users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('username')->nullable()->unique()->index();
            $table->string('name');
            $table->string('email')->unique()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('kyc_level')->default('level_0');
            $table->integer('onboarding_step')->default(0);
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('kyc_status')->default('none')->index();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Password Reset Tokens
        Schema::create('srv_auth_password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. User Sessions
        Schema::create('srv_auth_sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 4. Personal Access Tokens (Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('tokenable_type');
            $table->uuid('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });

        Schema::create('sys_personal_access_tokens', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('tokenable_type');
            $table->uuid('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });

        // 5. System Settings
        Schema::create('sys_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // 6. Languages
        Schema::create('sys_languages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->string('flag', 16)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_rtl')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 7. Translations
        Schema::create('sys_translations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('translatable_type')->nullable()->index();
            $table->uuid('translatable_id')->nullable()->index();
            $table->string('language_code', 10)->index();
            $table->string('field')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'language_code', 'field'], 'sys_translations_unique');
        });

        // 8. System Extensions & Logs
        Schema::create('sys_extensions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique();
            $table->string('type', 20)->default('plugin');
            $table->string('name', 150);
            $table->string('version', 30)->default('1.0.0');
            $table->string('database_version', 30)->default('1.0.0');
            $table->string('status', 20)->default('inactive');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_core')->default(false);
            $table->boolean('is_system')->default(false);
            $table->string('author', 100)->nullable();
            $table->string('author_url')->nullable();
            $table->string('plugin_url')->nullable();
            $table->string('main_file')->nullable();
            $table->text('description')->nullable();
            $table->string('license', 50)->default('MIT');
            $table->json('requirements')->nullable();
            $table->json('manifest')->nullable();
            $table->json('settings')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sys_extension_logs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('extension_slug', 100);
            $table->string('action', 30);
            $table->string('version_before', 30)->nullable();
            $table->string('version_after', 30)->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->uuid('performed_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sys_plugins', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('plugin_url')->nullable();
            $table->string('main_file')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        // 9. System Features (Feature Flags attached to extensions)
        Schema::create('sys_features', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('extension_slug', 100);
            $table->string('slug', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('category', 50)->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('extension_slug')
                ->references('slug')
                ->on('sys_extensions')
                ->onDelete('cascade');
        });

        // 10. System Notifications
        Schema::create('sys_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        // 11. Activity Logs (Model ActivityLog and Spatie ActivityLog)
        Schema::create('system_activity_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('action')->index();
            $table->string('model_id')->nullable();
            $table->string('model_type')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['model_id', 'model_type']);
        });

        Schema::create('sys_activity_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });

        // 12. User Login Histories
        Schema::create('sys_login_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->integer('session_duration')->nullable();
            $table->string('status')->default('success');
            $table->text('failure_reason')->nullable();
            $table->string('device_type')->nullable();
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // 13. Failed Jobs
        Schema::create('sys_failed_jobs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // 14. Redis Settings
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

        // 15. Two Factor Auth
        Schema::create('sys_two_factor_auth', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->text('secret')->nullable();
            $table->json('backup_codes')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('recovery_codes_generated_at')->nullable();
            $table->timestamps();
        });

        // 16. Email Templates
        Schema::create('sys_email_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('subject');
            $table->longText('body');
            $table->longText('text_body')->nullable();
            $table->json('variables')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 17. Content Templates
        Schema::create('sys_content_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('type')->default('post');
            $table->text('title_template')->nullable();
            $table->longText('body_template')->nullable();
            $table->text('excerpt_template')->nullable();
            $table->json('default_fields')->nullable();
            $table->json('meta')->nullable();
            $table->uuid('category_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->uuid('author_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 18. Custom Field Groups & Fields
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
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_content_custom_fields');
        Schema::dropIfExists('sys_custom_fields');
        Schema::dropIfExists('sys_field_groups');
        Schema::dropIfExists('sys_content_templates');
        Schema::dropIfExists('sys_email_templates');
        Schema::dropIfExists('sys_two_factor_auth');
        Schema::dropIfExists('sys_redis_settings');
        Schema::dropIfExists('sys_failed_jobs');
        Schema::dropIfExists('sys_login_histories');
        Schema::dropIfExists('sys_activity_logs');
        Schema::dropIfExists('system_activity_logs');
        Schema::dropIfExists('sys_notifications');
        Schema::dropIfExists('sys_features');
        Schema::dropIfExists('sys_plugins');
        Schema::dropIfExists('sys_extension_logs');
        Schema::dropIfExists('sys_extensions');
        Schema::dropIfExists('sys_translations');
        Schema::dropIfExists('sys_languages');
        Schema::dropIfExists('sys_settings');
        Schema::dropIfExists('sys_personal_access_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('srv_auth_sessions');
        Schema::dropIfExists('srv_auth_password_reset_tokens');
        Schema::dropIfExists('srv_auth_users');
    }
};
