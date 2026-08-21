<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Spatie Permissions & Roles (UUID-based)
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        if (empty($tableNames)) {
            $tableNames = [
                'roles' => 'srv_auth_roles',
                'permissions' => 'srv_auth_permissions',
                'model_has_permissions' => 'srv_auth_model_has_permissions',
                'model_has_roles' => 'srv_auth_model_has_roles',
                'role_has_permissions' => 'srv_auth_role_has_permissions',
            ];
        }

        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        Schema::create($tableNames['permissions'], function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $permissionPivotKey, $modelMorphKey): void {
            $table->uuid($permissionPivotKey);
            $table->string('model_type');
            $table->uuid($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($permissionPivotKey)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(
                [$permissionPivotKey, $modelMorphKey, 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $rolePivotKey, $modelMorphKey): void {
            $table->uuid($rolePivotKey);
            $table->string('model_type');
            $table->uuid($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($rolePivotKey)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(
                [$rolePivotKey, $modelMorphKey, 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $permissionPivotKey, $rolePivotKey): void {
            $table->uuid($permissionPivotKey);
            $table->uuid($rolePivotKey);

            $table->foreign($permissionPivotKey)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($rolePivotKey)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$permissionPivotKey, $rolePivotKey], 'role_has_permissions_permission_id_role_id_primary');
        });

        // 2. WebAuthn Passkeys
        Schema::create('passkeys', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('name');
            $table->string('credential_id')->unique();
            $table->text('public_key');
            $table->unsignedBigInteger('counter')->default(0);
            $table->json('transports')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('srv_auth_users')->cascadeOnDelete();
        });

        // 3. KYC Submissions & Documents
        Schema::create('kyc_submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('status')->default('draft')->index();
            $table->string('full_name')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('srv_auth_users')->cascadeOnDelete();
        });

        Schema::create('kyc_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('submission_id')->nullable()->index();
            $table->uuid('user_id')->index();
            $table->string('type')->default('id_card');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('srv_auth_users')->cascadeOnDelete();
            $table->foreign('submission_id')->references('id')->on('kyc_submissions')->nullOnDelete();
        });

        // 4. ABAC Policies
        Schema::create('sys_abac_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('target_resource')->index();
            $table->string('action')->nullable();
            $table->json('conditions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Security Logs
        Schema::create('sec_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('severity')->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 6. IP Lists (Allowlist / Blocklist)
        Schema::create('sec_ip_lists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45)->index();
            $table->string('type')->default('blocklist');
            $table->text('reason')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['ip_address', 'type']);
        });

        // 7. CSP Reports
        Schema::create('sec_csp_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('document_uri');
            $table->string('referrer')->nullable();
            $table->string('violated_directive');
            $table->string('effective_directive')->nullable();
            $table->string('original_policy')->nullable();
            $table->string('disposition')->nullable();
            $table->string('status_code')->nullable();
            $table->json('raw_report')->nullable();
            $table->string('blocked_uri')->nullable();
            $table->string('source_file')->nullable();
            $table->integer('line_number')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status')->default('new')->index();
            $table->timestamps();
        });

        // 8. File Integrity Baselines
        Schema::create('sec_file_integrity_baselines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('file_path')->unique();
            $table->string('hash');
            $table->integer('file_size');
            $table->string('status')->default('ok')->index();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });

        // 9. Dependency Vulnerabilities
        Schema::create('sec_dependency_vulnerabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('package_name')->index();
            $table->string('current_version');
            $table->string('patched_versions')->nullable();
            $table->string('advisory_id')->index();
            $table->string('severity')->default('medium')->index();
            $table->text('title');
            $table->timestamps();
        });

        // 10. OAuth Tables
        Schema::create('oauth_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('client_id')->index();
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->uuid('user_id')->index();
            $table->uuid('client_id')->index();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_device_codes', function (Blueprint $table): void {
            $table->string('id', 100)->primary();
            $table->uuid('user_id')->index();
            $table->uuid('client_id')->index();
            $table->string('user_code', 8)->unique();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::dropIfExists('oauth_device_codes');
        Schema::dropIfExists('oauth_auth_codes');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('sec_dependency_vulnerabilities');
        Schema::dropIfExists('sec_file_integrity_baselines');
        Schema::dropIfExists('sec_csp_reports');
        Schema::dropIfExists('sec_ip_lists');
        Schema::dropIfExists('sec_logs');
        Schema::dropIfExists('sys_abac_policies');
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('kyc_submissions');
        Schema::dropIfExists('passkeys');

        if (!empty($tableNames)) {
            Schema::dropIfExists($tableNames['role_has_permissions']);
            Schema::dropIfExists($tableNames['model_has_roles']);
            Schema::dropIfExists($tableNames['model_has_permissions']);
            Schema::dropIfExists($tableNames['roles']);
            Schema::dropIfExists($tableNames['permissions']);
        }
    }
};
