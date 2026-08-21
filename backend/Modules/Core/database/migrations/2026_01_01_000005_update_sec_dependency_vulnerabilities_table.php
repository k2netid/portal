<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sec_dependency_vulnerabilities')) {
            Schema::table('sec_dependency_vulnerabilities', function (Blueprint $table): void {
                if (! Schema::hasColumn('sec_dependency_vulnerabilities', 'version')) {
                    $table->string('version')->nullable()->after('package_name');
                }
                if (! Schema::hasColumn('sec_dependency_vulnerabilities', 'cve')) {
                    $table->string('cve')->nullable()->index()->after('version');
                }
                if (! Schema::hasColumn('sec_dependency_vulnerabilities', 'fixed_in')) {
                    $table->string('fixed_in')->nullable()->after('severity');
                }
                if (! Schema::hasColumn('sec_dependency_vulnerabilities', 'status')) {
                    $table->string('status')->default('new')->index()->after('fixed_in');
                }
                if (! Schema::hasColumn('sec_dependency_vulnerabilities', 'source')) {
                    $table->string('source')->default('composer')->index()->after('status');
                }
                if (! Schema::hasColumn('sec_dependency_vulnerabilities', 'description')) {
                    $table->text('description')->nullable()->after('source');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sec_dependency_vulnerabilities')) {
            Schema::table('sec_dependency_vulnerabilities', function (Blueprint $table): void {
                $columns = ['version', 'cve', 'fixed_in', 'status', 'source', 'description'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('sec_dependency_vulnerabilities', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
