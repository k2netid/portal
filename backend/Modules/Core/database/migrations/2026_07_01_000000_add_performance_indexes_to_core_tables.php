<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Operational / Billing / Subscriptions
        Schema::table('platform_packages', function (Blueprint $table): void {
            $table->index('product_id');
        });

        Schema::table('platform_subscriptions', function (Blueprint $table): void {
            $table->index('product_id');
            $table->index('package_id');
        });

        Schema::table('platform_transactions', function (Blueprint $table): void {
            $table->index('subscription_id');
            $table->index('package_id');
        });

        Schema::table('platform_payment_webhook_deliveries', function (Blueprint $table): void {
            $table->index('transaction_id');
        });

        // 2. Forms Module
        Schema::table('frm_forms', function (Blueprint $table): void {
            $table->index('author_id');
        });

        // 3. Publishing Module
        Schema::table('pub_comments', function (Blueprint $table): void {
            $table->index('status');
            $table->index('locked_by');
        });

        Schema::table('pub_content_category', function (Blueprint $table): void {
            $table->index('category_id');
        });

        // 4. Library Module (Taxonomies & Custom Fields)
        Schema::table('lib_taggables', function (Blueprint $table): void {
            $table->index('tag_id');
        });

        Schema::table('lib_field_group_pivot', function (Blueprint $table): void {
            $table->index('group_id');
        });

        Schema::table('lib_categories', function (Blueprint $table): void {
            $table->index('parent_id');
            $table->index('author_id');
        });

        // 5. Member Module
        Schema::table('mbr_members', function (Blueprint $table): void {
            $table->index('user_id');
        });

        // 6. AI Module
        Schema::table('ai_usage_logs', function (Blueprint $table): void {
            $table->index('user_id');
        });

        // 7. Analytics Module
        Schema::table('srv_analytics_events', function (Blueprint $table): void {
            $table->index('content_id');
        });
    }

    public function down(): void
    {
        // 1. Operational / Billing / Subscriptions
        Schema::table('platform_packages', function (Blueprint $table): void {
            $table->dropIndex(['product_id']);
        });

        Schema::table('platform_subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['package_id']);
        });

        Schema::table('platform_transactions', function (Blueprint $table): void {
            $table->dropIndex(['subscription_id']);
            $table->dropIndex(['package_id']);
        });

        Schema::table('platform_payment_webhook_deliveries', function (Blueprint $table): void {
            $table->dropIndex(['transaction_id']);
        });

        // 2. Forms Module
        Schema::table('frm_forms', function (Blueprint $table): void {
            $table->dropIndex(['author_id']);
        });

        // 3. Publishing Module
        Schema::table('pub_comments', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['locked_by']);
        });

        Schema::table('pub_content_category', function (Blueprint $table): void {
            $table->dropIndex(['category_id']);
        });

        // 4. Library Module (Taxonomies & Custom Fields)
        Schema::table('lib_taggables', function (Blueprint $table): void {
            $table->dropIndex(['tag_id']);
        });

        Schema::table('lib_field_group_pivot', function (Blueprint $table): void {
            $table->dropIndex(['group_id']);
        });

        Schema::table('lib_categories', function (Blueprint $table): void {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['author_id']);
        });

        // 5. Member Module
        Schema::table('mbr_members', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
        });

        // 6. AI Module
        Schema::table('ai_usage_logs', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
        });

        // 7. Analytics Module
        Schema::table('srv_analytics_events', function (Blueprint $table): void {
            $table->dropIndex(['content_id']);
        });
    }
};
