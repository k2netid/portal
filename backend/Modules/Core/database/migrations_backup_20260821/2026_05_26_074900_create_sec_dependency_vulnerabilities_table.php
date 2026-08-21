<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sec_dependency_vulnerabilities')) {
            return;
        }

        Schema::create('sec_dependency_vulnerabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('package_name');
            $table->string('version')->nullable();
            $table->string('severity')->index();
            $table->string('cve')->nullable();
            $table->string('fixed_in')->nullable();
            $table->string('status')->default('new')->index();
            $table->string('source')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['package_name', 'version', 'cve'], 'sec_dep_vuln_pkg_ver_cve_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sec_dependency_vulnerabilities');
    }
};
