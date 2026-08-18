<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Operational / Billing / Subscriptions (if tables exist)
        if (Schema::hasTable('platform_packages')) {
            Schema::table('platform_packages', function (Blueprint $table): void {
                $table->index('product_id');
            });
        }

        if (Schema::hasTable('platform_subscriptions')) {
            Schema::table('platform_subscriptions', function (Blueprint $table): void {
                $table->index('product_id');
                $table->index('package_id');
            });
        }

        if (Schema::hasTable('platform_transactions')) {
            Schema::table('platform_transactions', function (Blueprint $table): void {
                $table->index('subscription_id');
                $table->index('package_id');
            });
        }

        if (Schema::hasTable('platform_payment_webhook_deliveries')) {
            Schema::table('platform_payment_webhook_deliveries', function (Blueprint $table): void {
                $table->index('transaction_id');
            });
        }

        // 2. Forms Module
        if (Schema::hasTable('frm_forms')) {
            Schema::table('frm_forms', function (Blueprint $table): void {
                $table->index('author_id');
            });
        }

        // 3. Publishing Module
        if (Schema::hasTable('pub_comments')) {
            Schema::table('pub_comments', function (Blueprint $table): void {
                $table->index('status');
                $table->index('locked_by');
            });
        }

        if (Schema::hasTable('pub_content_category')) {
            Schema::table('pub_content_category', function (Blueprint $table): void {
                $table->index('category_id');
            });
        }

        // 4. Library Module (Taxonomies & Custom Fields)
        if (Schema::hasTable('lib_taggables')) {
            Schema::table('lib_taggables', function (Blueprint $table): void {
                $table->index('tag_id');
            });
        }

        if (Schema::hasTable('lib_field_group_pivot')) {
            Schema::table('lib_field_group_pivot', function (Blueprint $table): void {
                $table->index('group_id');
            });
        }

        if (Schema::hasTable('lib_categories')) {
            Schema::table('lib_categories', function (Blueprint $table): void {
                $table->index('parent_id');
                $table->index('author_id');
            });
        }

        // 5. Member Module
        if (Schema::hasTable('mbr_members')) {
            Schema::table('mbr_members', function (Blueprint $table): void {
                $table->index('user_id');
            });
        }

        // 6. AI Module
        if (Schema::hasTable('ai_usage_logs')) {
            Schema::table('ai_usage_logs', function (Blueprint $table): void {
                $table->index('user_id');
            });
        }

        // 7. Analytics Module
        if (Schema::hasTable('srv_analytics_events')) {
            Schema::table('srv_analytics_events', function (Blueprint $table): void {
                $table->index('content_id');
            });
        }
    }

    public function down(): void
    {
        // 1. Operational / Billing / Subscriptions
        if (Schema::hasTable('platform_packages')) {
            Schema::table('platform_packages', function (Blueprint $table): void {
                $table->dropIndex(['product_id']);
            });
        }

        if (Schema::hasTable('platform_subscriptions')) {
            Schema::table('platform_subscriptions', function (Blueprint $table): void {
                $table->dropIndex(['product_id']);
                $table->dropIndex(['package_id']);
            });
        }

        if (Schema::hasTable('platform_transactions')) {
            Schema::table('platform_transactions', function (Blueprint $table): void {
                $table->dropIndex(['subscription_id']);
                $table->dropIndex(['package_id']);
            });
        }

        if (Schema::hasTable('platform_payment_webhook_deliveries')) {
            Schema::table('platform_payment_webhook_deliveries', function (Blueprint $table): void {
                $table->dropIndex(['transaction_id']);
            });
        }

        // 2. Forms Module
        if (Schema::hasTable('frm_forms')) {
            Schema::table('frm_forms', function (Blueprint $table): void {
                $table->dropIndex(['author_id']);
            });
        }

        // 3. Publishing Module
        if (Schema::hasTable('pub_comments')) {
            Schema::table('pub_comments', function (Blueprint $table): void {
                $table->dropIndex(['status']);
                $table->dropIndex(['locked_by']);
            });
        }

        if (Schema::hasTable('pub_content_category')) {
            Schema::table('pub_content_category', function (Blueprint $table): void {
                $table->dropIndex(['category_id']);
            });
        }

        // 4. Library Module (Taxonomies & Custom Fields)
        if (Schema::hasTable('lib_taggables')) {
            Schema::table('lib_taggables', function (Blueprint $table): void {
                $table->dropIndex(['tag_id']);
            });
        }

        if (Schema::hasTable('lib_field_group_pivot')) {
            Schema::table('lib_field_group_pivot', function (Blueprint $table): void {
                $table->dropIndex(['group_id']);
            });
        }

        if (Schema::hasTable('lib_categories')) {
            Schema::table('lib_categories', function (Blueprint $table): void {
                $table->dropIndex(['parent_id']);
                $table->dropIndex(['author_id']);
            });
        }

        // 5. Member Module
        if (Schema::hasTable('mbr_members')) {
            Schema::table('mbr_members', function (Blueprint $table): void {
                $table->dropIndex(['user_id']);
            });
        }

        // 6. AI Module
        if (Schema::hasTable('ai_usage_logs')) {
            Schema::table('ai_usage_logs', function (Blueprint $table): void {
                $table->dropIndex(['user_id']);
            });
        }

        // 7. Analytics Module
        if (Schema::hasTable('srv_analytics_events')) {
            Schema::table('srv_analytics_events', function (Blueprint $table): void {
                $table->dropIndex(['content_id']);
            });
        }
    }
};
