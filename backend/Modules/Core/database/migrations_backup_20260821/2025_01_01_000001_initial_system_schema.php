<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Settings Table
        Schema::create('sys_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['key']);
        });

        // 2. Activity Logs
        Schema::create('system_activity_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('action')->index();
            $table->string('model_id')->nullable();
            $table->string('model_type')->nullable();
            $table->index(['model_id', 'model_type']);
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 3. User Login History
        Schema::create('sys_login_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->integer('session_duration')->nullable();
            $table->string('status')->default('success');
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        // 4. Plugins Registry
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

        // 5. Auth Users
        Schema::create('srv_auth_users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srv_auth_users');
        Schema::dropIfExists('sys_plugins');
        Schema::dropIfExists('sys_login_histories');
        Schema::dropIfExists('system_activity_logs');
        Schema::dropIfExists('sys_settings');
    }
};
