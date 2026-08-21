<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix sys_email_templates
        Schema::table('sys_email_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('sys_email_templates', 'text_body')) {
                $table->longText('text_body')->nullable()->after('body');
            }
            if (! Schema::hasColumn('sys_email_templates', 'category')) {
                $table->string('category')->nullable()->after('variables');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sys_email_templates', function (Blueprint $table): void {
            $table->dropColumn(['text_body', 'category']);
        });
    }
};
