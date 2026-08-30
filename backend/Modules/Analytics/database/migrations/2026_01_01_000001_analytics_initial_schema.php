<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Analytics Sessions
        Schema::create('srv_analytics_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('session_id')->unique();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->integer('page_views')->default(0);
            $table->integer('duration')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamps();
        });

        // 2. Analytics Visits (Individual page views)
        Schema::create('srv_analytics_visits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('session_id')->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->default('GET');
            $table->integer('status_code')->default(200);
            $table->integer('duration')->nullable(); // Page load time in ms
            $table->timestamp('visited_at')->useCurrent();
            $table->timestamps();
        });

        // 3. Analytics Events
        Schema::create('srv_analytics_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('session_id')->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('event_name')->index();
            $table->string('event_category')->nullable();
            $table->json('event_data')->nullable();
            $table->string('url')->nullable();
            $table->uuid('content_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });

        // 4. Slow Queries
        Schema::create('srv_analytics_slow_queries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->text('sql');
            $table->float('time');
            $table->string('connection')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('srv_analytics_slow_queries');
        Schema::dropIfExists('srv_analytics_events');
        Schema::dropIfExists('srv_analytics_visits');
        Schema::dropIfExists('srv_analytics_sessions');
    }
};
