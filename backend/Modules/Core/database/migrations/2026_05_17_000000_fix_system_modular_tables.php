<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix sys_content_templates
        Schema::table('sys_content_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_content_templates', 'type')) {
                $table->string('type')->default('post')->after('slug');
            }
            if (! Schema::hasColumn('sys_content_templates', 'title_template')) {
                $table->string('title_template')->nullable()->after('type');
            }
            if (! Schema::hasColumn('sys_content_templates', 'body_template')) {
                $table->longText('body_template')->nullable()->after('title_template');
            }
            if (! Schema::hasColumn('sys_content_templates', 'excerpt_template')) {
                $table->text('excerpt_template')->nullable()->after('body_template');
            }
            if (! Schema::hasColumn('sys_content_templates', 'default_fields')) {
                $table->json('default_fields')->nullable()->after('excerpt_template');
            }
            if (! Schema::hasColumn('sys_content_templates', 'usage_count')) {
                $table->unsignedInteger('usage_count')->default(0)->after('is_active');
            }
            if (! Schema::hasColumn('sys_content_templates', 'author_id')) {
                $table->uuid('author_id')->nullable()->index()->after('usage_count');
            }

            // Cleanup old column if it exists
            if (Schema::hasColumn('sys_content_templates', 'content')) {
                // We keep it for now but it's redundant
            }
        });

        // Fix sys_scheduled_tasks
        Schema::table('sys_scheduled_tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_scheduled_tasks', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'status')) {
                $table->string('status')->default('inactive')->after('is_active');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'output')) {
                $table->longText('output')->nullable()->after('status');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'options')) {
                $table->json('options')->nullable()->after('output');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'usage_count')) {
                $table->unsignedInteger('usage_count')->default(0)->after('options');
            }
            if (! Schema::hasColumn('sys_scheduled_tasks', 'author_id')) {
                $table->uuid('author_id')->nullable()->index()->after('usage_count');
            }
        });

        // Fix sys_email_templates
        Schema::table('sys_email_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_email_templates', 'usage_count')) {
                $table->unsignedInteger('usage_count')->default(0)->after('is_active');
            }
            if (! Schema::hasColumn('sys_email_templates', 'author_id')) {
                $table->uuid('author_id')->nullable()->index()->after('usage_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sys_content_templates', function (Blueprint $table): void {
            $table->dropColumn(['type', 'title_template', 'body_template', 'excerpt_template', 'default_fields', 'usage_count', 'author_id']);
        });

        Schema::table('sys_scheduled_tasks', function (Blueprint $table): void {
            $table->dropColumn(['description', 'status', 'output', 'options', 'usage_count', 'author_id']);
        });

        Schema::table('sys_email_templates', function (Blueprint $table): void {
            $table->dropColumn(['usage_count', 'author_id']);
        });
    }
};
