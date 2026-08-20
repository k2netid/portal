<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sys_abac_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('target_resource')->index()->comment('Target ability/resource name, e.g. "financial_reports"');
            $table->string('action')->nullable()->comment('Specific action on resource, e.g. "view", "export"');
            $table->json('conditions')->comment('JSON rules array: [{"attribute": "user.kyc_level", "operator": ">=", "value": 2}]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_abac_policies');
    }
};
