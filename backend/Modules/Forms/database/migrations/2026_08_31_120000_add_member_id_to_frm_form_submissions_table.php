<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frm_form_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('frm_form_submissions', 'member_id')) {
                $table->uuid('member_id')->nullable()->index()->after('user_id');
                $table->foreign('member_id')->references('id')->on('mem_members')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('frm_form_submissions', function (Blueprint $table): void {
            if (Schema::hasColumn('frm_form_submissions', 'member_id')) {
                $table->dropForeign(['member_id']);
                $table->dropColumn('member_id');
            }
        });
    }
};
